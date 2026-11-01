<?php

namespace App\Services;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Exceptions\AturanBisnisException;
use App\Exceptions\StokTidakCukupException;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Service stok: satu-satunya pintu perubahan saldo & ledger mutasi (PRD §7.4).
 *
 * Aturan yang diimplementasikan:
 *  - KG = single source of truth (A1/A1b): qty yang diterima parameter
 *    adalah kg timbangan, tidak pernah hasil konversi bobbin/box.
 *  - Dua pos stok (A3, §5.2.1): `stok_aktif` & `stok_buffer`.
 *    Pemisahan pos HANYA relevan untuk POY; DTY & Pendukung selalu
 *    masuk `stok_aktif` dan `stok_buffer` tetap 0.
 *  - Setiap perubahan saldo menulis satu baris `mutasi_stok` dengan
 *    `pos`, `masuk_qty`/`keluar_qty`, dan `saldo_akhir_qty` = snapshot
 *    saldo pos tersebut SETELAH mutasi (bukan sebelum).
 *  - Perpindahan pos (ALOKASI_BUFFER / RILIS_BUFFER) menulis DUA baris
 *    mutasi atomik (keluar dari pos asal, masuk ke pos tujuan).
 *  - Semua operasi tulis berada di DB::transaction() dan mengunci baris
 *    `stok_barang` yang relevan dengan lockForUpdate() (PRD §7.5).
 *  - Stok tidak boleh negatif: divalidasi service (DB juga punya CHECK).
 *  - Grade wajib se-kategori dengan barang (PRD §7.5).
 */
class StokService
{
    /**
     * Tambah saldo pada pos tertentu dan tulis mutasi masuk.
     *
     * @param  float  $qty  berat bersih (kg) hasil timbangan / dokumen — BUKAN konversi kemasan.
     * @param  PosStok|string  $pos  pos tujuan (AKTIF / BUFFER).
     * @param  JenisMutasi|string  $jenisMutasi  jenis mutasi ledger.
     * @param  array{jumlah_bobbin?: int, jumlah_box?: int}  $kemasan  atribut pelengkap (bukan sumber saldo).
     */
    public function tambahStok(
        Barang $barang,
        string $merge,
        int $gradeId,
        float $qty,
        PosStok|string $pos = PosStok::Aktif,
        JenisMutasi|string $jenisMutasi = JenisMutasi::Masuk,
        string $refNomor = '',
        ?int $userId = null,
        ?CarbonInterface $tanggal = null,
        array $kemasan = [],
    ): StokBarang {
        $pos = $this->pos($pos);
        $jenisMutasi = $this->jenisMutasi($jenisMutasi);

        $this->validasiQtyPositif($qty);
        $this->validasiGrade($barang, $gradeId);
        $this->validasiBufferHanyaPoy($barang, $pos);

        return DB::transaction(function () use (
            $barang, $merge, $gradeId, $qty, $pos, $jenisMutasi, $refNomor, $userId, $tanggal, $kemasan
        ): StokBarang {
            $stok = $this->kunciBarisStok($barang, $merge, $gradeId);

            $kolom = $this->kolomSaldo($pos);

            $stok->{$kolom} = (float) $stok->{$kolom} + $qty;

            // Atribut pelengkap (bobbin/box) hanya untuk POY/DTY, bukan akuntansi.
            if (array_key_exists('jumlah_bobbin', $kemasan) && $kemasan['jumlah_bobbin'] !== null) {
                $stok->jumlah_bobbin = (int) $stok->jumlah_bobbin + (int) $kemasan['jumlah_bobbin'];
            }
            if (array_key_exists('jumlah_box', $kemasan) && $kemasan['jumlah_box'] !== null) {
                $stok->jumlah_box = (int) $stok->jumlah_box + (int) $kemasan['jumlah_box'];
            }

            $stok->save();

            $this->catatMutasi(
                $stok, $pos, $jenisMutasi, $refNomor, $userId, $tanggal,
                masuk: $qty,
                keluar: 0.0,
            );

            return $stok;
        });
    }

    /**
     * Kurangi saldo pada pos tertentu bila cukup, lalu tulis mutasi keluar.
     *
     * @throws StokTidakCukupException bila saldo pos < qty.
     */
    public function kurangiStok(
        Barang $barang,
        string $merge,
        int $gradeId,
        float $qty,
        PosStok|string $pos = PosStok::Aktif,
        JenisMutasi|string $jenisMutasi = JenisMutasi::Keluar,
        string $refNomor = '',
        ?int $userId = null,
        ?CarbonInterface $tanggal = null,
    ): StokBarang {
        $pos = $this->pos($pos);
        $jenisMutasi = $this->jenisMutasi($jenisMutasi);

        $this->validasiQtyPositif($qty);
        $this->validasiGrade($barang, $gradeId);

        return DB::transaction(function () use (
            $barang, $merge, $gradeId, $qty, $pos, $jenisMutasi, $refNomor, $userId, $tanggal
        ): StokBarang {
            $stok = $this->kunciBarisStok($barang, $merge, $gradeId);

            $kolom = $this->kolomSaldo($pos);
            $tersedia = (float) $stok->{$kolom};

            if ($tersedia + 1e-9 < $qty) {
                throw new StokTidakCukupException($barang->kode_barang, $pos, $qty, $tersedia);
            }

            $stok->{$kolom} = round($tersedia - $qty, 2);
            $stok->save();

            $this->catatMutasi(
                $stok, $pos, $jenisMutasi, $refNomor, $userId, $tanggal,
                masuk: 0.0,
                keluar: $qty,
            );

            return $stok;
        });
    }

    /**
     * Pindahkan porsi stok AKTIF -> BUFFER (PRD §7.4, ALOKASI_BUFFER).
     *
     * Menulis DUA baris mutasi:
     *   1. pos AKTIF  — keluar_qty = porsi
     *   2. pos BUFFER — masuk_qty  = porsi
     * Masing-masing dengan saldo_akhir_qty pos tersebut setelah perubahan.
     */
    public function alokasiKeBuffer(
        Barang $barang,
        string $merge,
        int $gradeId,
        float $porsi,
        string $refNomor = '',
        ?int $userId = null,
        ?CarbonInterface $tanggal = null,
    ): void {
        $this->validasiQtyPositif($porsi);
        $this->validasiGrade($barang, $gradeId);

        if (! $barang->memakaiBuffer()) {
            throw new AturanBisnisException(
                "Barang {$barang->kode_barang} tidak memakai pemisahan pos buffer (hanya kategori POY)."
            );
        }

        DB::transaction(function () use ($barang, $merge, $gradeId, $porsi, $refNomor, $userId, $tanggal): void {
            $stok = $this->kunciBarisStok($barang, $merge, $gradeId);

            $aktif = (float) $stok->stok_aktif;

            if ($aktif + 1e-9 < $porsi) {
                throw new StokTidakCukupException($barang->kode_barang, PosStok::Aktif, $porsi, $aktif);
            }

            // 1) Keluar dari AKTIF.
            $stok->stok_aktif = round($aktif - $porsi, 2);
            $stok->save();
            $this->catatMutasi(
                $stok, PosStok::Aktif, JenisMutasi::AlokasiBuffer, $refNomor, $userId, $tanggal,
                masuk: 0.0,
                keluar: $porsi,
            );

            // 2) Masuk ke BUFFER.
            $stok->stok_buffer = round((float) $stok->stok_buffer + $porsi, 2);
            $stok->save();
            $this->catatMutasi(
                $stok, PosStok::Buffer, JenisMutasi::AlokasiBuffer, $refNomor, $userId, $tanggal,
                masuk: $porsi,
                keluar: 0.0,
            );
        });
    }

    /**
     * Pindahkan stok BUFFER -> AKTIF (PRD §7.4, RILIS_BUFFER).
     *
     * Menulis DUA baris mutasi: BUFFER keluar, AKTIF masuk.
     *
     * @throws StokTidakCukupException bila saldo buffer < qty.
     */
    public function rilisBuffer(
        Barang $barang,
        string $merge,
        int $gradeId,
        float $qty,
        string $refNomor = '',
        ?int $userId = null,
        ?CarbonInterface $tanggal = null,
    ): void {
        $this->validasiQtyPositif($qty);
        $this->validasiGrade($barang, $gradeId);

        DB::transaction(function () use ($barang, $merge, $gradeId, $qty, $refNomor, $userId, $tanggal): void {
            $stok = $this->kunciBarisStok($barang, $merge, $gradeId);

            $buffer = (float) $stok->stok_buffer;

            if ($buffer + 1e-9 < $qty) {
                throw new StokTidakCukupException($barang->kode_barang, PosStok::Buffer, $qty, $buffer);
            }

            // 1) Keluar dari BUFFER.
            $stok->stok_buffer = round($buffer - $qty, 2);
            $stok->save();
            $this->catatMutasi(
                $stok, PosStok::Buffer, JenisMutasi::RilisBuffer, $refNomor, $userId, $tanggal,
                masuk: 0.0,
                keluar: $qty,
            );

            // 2) Masuk ke AKTIF.
            $stok->stok_aktif = round((float) $stok->stok_aktif + $qty, 2);
            $stok->save();
            $this->catatMutasi(
                $stok, PosStok::Aktif, JenisMutasi::RilisBuffer, $refNomor, $userId, $tanggal,
                masuk: $qty,
                keluar: 0.0,
            );
        });
    }

    /**
     * Total stok BUFFER gudang (kg) untuk barang bertanda is_merge_minggu.
     *
     * Ini akumulasi TOTAL GUDANG lintas semua merge (A12), bukan per merge.
     * Menjadi dasar penentuan sisa kuota buffer di AlokasiBufferService.
     */
    public function totalBufferMinggu(?int $kecualiBarangId = null): float
    {
        return $this->queryBufferMinggu($kecualiBarangId)
            ->sum('stok_buffer') ?: 0.0;
    }

    /**
     * Total stok BUFFER gudang dalam satuan pallet POY (threshold visual, A2).
     */
    public function totalBufferMingguPallet(?int $kecualiBarangId = null): float
    {
        $kgPerPallet = (float) config('wms.pallet.poy_kg');

        return $kgPerPallet > 0 ? $this->totalBufferMinggu($kecualiBarangId) / $kgPerPallet : 0.0;
    }

    /**
     * Saldo stok satu kombinasi (barang, merge, grade). Null bila belum ada.
     */
    public function saldo(Barang $barang, string $merge, int $gradeId): ?StokBarang
    {
        return StokBarang::query()
            ->posisi($barang->id, $merge, $gradeId)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil & kunci baris stok (firstOrCreate + lockForUpdate).
     *
     * UNIQUE (barang_id, merge, grade_id) dijaga dengan firstOrCreate di
     * dalam transaction; setelah itu baris selalu ada sehingga
     * lockForUpdate() dijamin mengenai tepat satu baris.
     */
    private function kunciBarisStok(Barang $barang, string $merge, int $gradeId): StokBarang
    {
        $merge = trim($merge);

        if ($merge === '') {
            throw new AturanBisnisException('Merge wajib diisi untuk setiap mutasi stok.');
        }

        StokBarang::firstOrCreate(
            [
                'barang_id' => $barang->id,
                'merge' => $merge,
                'grade_id' => $gradeId,
            ],
            [
                'stok_aktif' => 0,
                'stok_buffer' => 0,
            ]
        );

        return StokBarang::query()
            ->posisi($barang->id, $merge, $gradeId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Tulis satu baris ledger mutasi dengan snapshot saldo pos setelah mutasi.
     */
    private function catatMutasi(
        StokBarang $stok,
        PosStok $pos,
        JenisMutasi $jenisMutasi,
        string $refNomor,
        ?int $userId,
        ?CarbonInterface $tanggal,
        float $masuk,
        float $keluar,
    ): MutasiStok {
        $kolom = $this->kolomSaldo($pos);

        return MutasiStok::create([
            'barang_id' => $stok->barang_id,
            'merge' => $stok->merge,
            'grade_id' => $stok->grade_id,
            'tanggal' => $tanggal ?? now(),
            'jenis_transaksi' => $jenisMutasi,
            'pos' => $pos,
            'ref_nomor' => mb_substr($refNomor, 0, 50),
            'masuk_qty' => $masuk,
            'keluar_qty' => $keluar,
            'saldo_akhir_qty' => (float) $stok->{$kolom},
            'user_id' => $userId,
        ]);
    }

    /**
     * Query dasar saldo buffer merge Minggu (lintas semua merge).
     */
    private function queryBufferMinggu(?int $kecualiBarangId = null): Builder
    {
        return StokBarang::query()
            ->hanyaMergeMinggu()
            ->whereHas('barang', fn (Builder $q) => $q->whereHas(
                'kategori',
                fn (Builder $k) => $k->where('kode', KategoriBarang::POY)
            ))
            ->when($kecualiBarangId !== null, fn (Builder $q) => $q->where('barang_id', '!=', $kecualiBarangId))
            ->where('stok_buffer', '>', 0);
    }

    private function kolomSaldo(PosStok $pos): string
    {
        return $pos === PosStok::Buffer ? 'stok_buffer' : 'stok_aktif';
    }

    private function pos(PosStok|string $pos): PosStok
    {
        return $pos instanceof PosStok ? $pos : PosStok::from($pos);
    }

    private function jenisMutasi(JenisMutasi|string $jenis): JenisMutasi
    {
        return $jenis instanceof JenisMutasi ? $jenis : JenisMutasi::from($jenis);
    }

    private function validasiQtyPositif(float $qty): void
    {
        if ($qty <= 0) {
            throw new AturanBisnisException('Kuantitas mutasi stok harus lebih besar dari 0.');
        }
    }

    /**
     * PRD §7.5: grade yang dipakai wajib milik kategori yang sama dengan barang.
     */
    private function validasiGrade(Barang $barang, int $gradeId): void
    {
        $sesuai = Grade::query()
            ->whereKey($gradeId)
            ->where('kategori_id', $barang->kategori_id)
            ->exists();

        if (! $sesuai) {
            throw AturanBisnisException::gradeTidakSesuaiKategori((string) $barang->kode_barang);
        }
    }

    /**
     * Pemisahan pos buffer hanya untuk POY (A3 / PRD §7.2).
     * DTY & item pendukung tidak boleh punya saldo buffer.
     */
    private function validasiBufferHanyaPoy(Barang $barang, PosStok $pos): void
    {
        if ($pos === PosStok::Buffer && ! $barang->memakaiBuffer()) {
            throw new AturanBisnisException(
                "Barang {$barang->kode_barang} tidak memakai stok buffer — seluruh stok masuk pos AKTIF."
            );
        }
    }
}
