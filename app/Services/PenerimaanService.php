<?php

namespace App\Services;

use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Orkestrasi transaksi masuk: status DRAFT -> POSTED / ISSUED.
 *
 * Service ini TIDAK menggantikan StokService / AlokasiBufferService, hanya
 * merangkai keduanya dalam SATU DB transaction:
 *
 *   POSTING (POY_IPCI / DTY_PRODUKSI), target POSTED:
 *     1. tambahStok(detail, PosStok::AKTIF) untuk setiap baris detail
 *     2. AlokasiBufferService::prosesAlokasiUntukPenerimaan()
 *
 *   ISSUE (PENDUKUNG_GATEPASS), target ISSUED:
 *     1. tambahStok(detail, PosStok::AKTIF) untuk setiap baris detail
 *
 * Keamanan double-posting:
 *  - Seluruh pipeline berjalan di dalam DB::transaction().
 *  - Baris `transaksi_masuk` dikunci dengan lockForUpdate() lalu status
 *    DIPERIKSA ULANG setelah lock didapat. Request kedua yang menunggu lock
 *    akan melihat status POSTED/ISSUED dan langsung ditolak
 *    (AturanBisnisException) — bukan menambah stok dua kali.
 *  - StokService sendiri juga mengunci baris `stok_barang` (lockForUpdate).
 *
 * Semua konstanta berasal dari config('wms...'); tidak ada angka hardcode.
 */
class PenerimaanService
{
    /**
     * Kode satuan kemasan box (data referensi `satuan`, bukan konstanta bisnis
     * angka — nilai kilogram per box tetap dari config/wms.php).
     */
    public const SATUAN_BOX = 'BOX';

    public function __construct(
        private readonly StokService $stokService,
        private readonly AlokasiBufferService $alokasiBufferService,
        private readonly KonversiSatuanService $konversiService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Mapping jenis -> modul ActivityLog
    |--------------------------------------------------------------------------
    */

    public static function modul(JenisMasuk $jenis): string
    {
        return match ($jenis) {
            JenisMasuk::PoyIpci => 'TRANSAKSI_MASUK_POY',
            JenisMasuk::PendukungGatepass => 'TRANSAKSI_MASUK_PENDUKUNG',
            JenisMasuk::DtyProduksi => 'TRANSAKSI_MASUK_DTY',
            default => 'TRANSAKSI_MASUK',
        };
    }

    /**
     * Pemetaan jenis_masuk -> kode kategori barang yang boleh dipakai detail.
     *
     * Mencegah silang jenis: POY hanya barang POY, DTY hanya DTY, sedangkan
     * Gate Pass hanya item pendukung.
     */
    public static function kodeKategori(JenisMasuk $jenis): string
    {
        return match ($jenis) {
            JenisMasuk::PoyIpci => KategoriBarang::POY,
            JenisMasuk::DtyProduksi => KategoriBarang::DTY,
            JenisMasuk::PendukungGatepass => KategoriBarang::PENDUKUNG,
            default => KategoriBarang::POY,
        };
    }

    /**
     * Status target yang sah untuk jenis transaksi:
     * dokumen resmi (Gate Pass) -> ISSUED, transaksi internal -> POSTED.
     */
    public static function statusTarget(JenisMasuk $jenis): StatusDokumen
    {
        return $jenis->isDokumenResmi() ? StatusDokumen::Issued : StatusDokumen::Posted;
    }

    /*
    |--------------------------------------------------------------------------
    | POSTING / ISSUE
    |--------------------------------------------------------------------------
    */

    /**
     * Jalankan posting (DRAFT -> POSTED) atau issue (DRAFT -> ISSUED).
     *
     * @return StatusDokumen status akhir (POSTED atau ISSUED)
     *
     * @throws AturanBisnisException bila status bukan DRAFT, jenis tidak cocok,
     *                               atau validasi bisnis detail gagal.
     */
    public function posting(TransaksiMasuk $transaksi, ?int $userId = null): StatusDokumen
    {
        $target = self::statusTarget($transaksi->jenis_masuk);

        return DB::transaction(function () use ($transaksi, $target, $userId): StatusDokumen {
            // Kunci baris header: hanya satu request yang boleh memproses.
            $terkunci = TransaksiMasuk::query()
                ->lockForUpdate()
                ->findOrFail($transaksi->getKey());

            if ($terkunci->status !== StatusDokumen::Draft) {
                throw new AturanBisnisException(
                    "Transaksi {$terkunci->nomor_transaksi} berstatus {$terkunci->status->value} "
                    .'dan tidak dapat diposting ulang. Hanya DRAFT yang dapat diposting.'
                );
            }

            if (! $terkunci->status->canTransitionTo($target)) {
                throw new AturanBisnisException(
                    "Transisi status {$terkunci->status->value} -> {$target->value} tidak diizinkan."
                );
            }

            /** @var Collection<int, TransaksiMasukDetail> $detailList */
            $detailList = $terkunci->detail()->with(['barang.kategori', 'grade', 'satuanKemasan'])->get();

            if ($detailList->isEmpty()) {
                throw new AturanBisnisException(
                    "Transaksi {$terkunci->nomor_transaksi} tidak memiliki baris detail — tidak dapat diposting."
                );
            }

            // Validasi bisnis akhir sebelum menyentuh stok (gagal -> seluruh
            // transaction rollback, status tetap DRAFT).
            $this->validasiDetail($terkunci, $detailList);

            $userId ??= $terkunci->created_by !== null ? (int) $terkunci->created_by : null;

            foreach ($detailList as $detail) {
                $this->stokService->tambahStok(
                    barang: $detail->barang,
                    merge: (string) $detail->merge,
                    gradeId: (int) $detail->grade_id,
                    qty: (float) $detail->berat_kg,
                    pos: PosStok::Aktif,
                    jenisMutasi: JenisMutasi::Masuk,
                    refNomor: (string) $terkunci->nomor_transaksi,
                    userId: $userId,
                    tanggal: $terkunci->tanggal,
                    kemasan: array_filter(
                        [
                            'jumlah_bobbin' => $detail->jumlah_bobbin,
                            'jumlah_box' => $this->jumlahBoxDariDetail($detail),
                        ],
                        fn ($nilai) => $nilai !== null && (int) $nilai > 0,
                    ),
                );
            }

            // Status harus POSTED LEBIH DULU sebelum alokasi buffer:
            // AlokasiBufferService menghitung "pasokan hari itu" dari SELURUH
            // transaksi berstatus mempengaruhiStok() (POSTED/ISSUED). Bila
            // status transaksi ini masih DRAFT, pasokan hari itu terbaca 0
            // sehingga alokasi selalu dilewati — buffer tidak pernah terisi.
            $terkunci->status = $target;
            $terkunci->save();

            // Alokasi buffer Minggu hanya untuk penerimaan POY (service sendiri
            // memfilter barang is_merge_minggu & hari kerja).
            // Masih berada di DB transaction yang sama -> atomik dengan stok.
            $this->alokasiBufferService->prosesAlokasiUntukPenerimaan($terkunci, $detailList);

            ActivityLog::catat(
                'UPDATE',
                self::modul($terkunci->jenis_masuk),
                (int) $terkunci->id,
                sprintf(
                    '%s %s (%s kg, %d baris) oleh user #%s.',
                    $target === StatusDokumen::Issued ? 'Issue dokumen' : 'Posting transaksi',
                    $terkunci->nomor_transaksi,
                    number_format($detailList->sum(fn (TransaksiMasukDetail $d) => (float) $d->berat_kg), 2, ',', '.'),
                    $detailList->count(),
                    (string) ($userId ?? '-'),
                ),
                ['user_id' => $userId],
            );

            return $target;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi bisnis per jenis
    |--------------------------------------------------------------------------
    */

    /**
     * @param  Collection<int, TransaksiMasukDetail>  $detailList
     *
     * @throws AturanBisnisException
     */
    public function validasiDetail(TransaksiMasuk $transaksi, Collection $detailList): void
    {
        $kodeKategori = self::kodeKategori($transaksi->jenis_masuk);

        foreach ($detailList as $urutan => $detail) {
            $label = 'Baris '.($urutan + 1);
            $barang = $detail->barang;

            if (! $barang instanceof Barang) {
                throw new AturanBisnisException("{$label}: barang tidak ditemukan.");
            }

            // 1) Jenis transaksi harus cocok dengan kategori barang.
            if ($barang->kategori?->kode !== $kodeKategori) {
                throw new AturanBisnisException(sprintf(
                    '%s: barang %s ber-kategori %s, tidak boleh dipakai pada transaksi %s (harus kategori %s).',
                    $label,
                    $barang->kode_barang,
                    (string) ($barang->kategori?->kode ?? '—'),
                    $transaksi->jenis_masuk->value,
                    $kodeKategori,
                ));
            }

            // 2) Grade wajib milik kategori barang yang sama.
            $grade = $detail->grade;

            if (! $grade instanceof Grade || (int) $grade->kategori_id !== (int) $barang->kategori_id) {
                throw AturanBisnisException::gradeTidakSesuaiKategori((string) $barang->kode_barang);
            }

            // 3) Merge wajib & berat kg wajib > 0.
            if (trim((string) $detail->merge) === '') {
                throw new AturanBisnisException("{$label}: merge wajib diisi.");
            }

            if ((float) $detail->berat_kg <= 0) {
                if ($transaksi->jenis_masuk === JenisMasuk::PoyIpci) {
                    throw AturanBisnisException::poyWajibKg();
                }

                throw new AturanBisnisException("{$label}: berat kg wajib diisi dan lebih besar dari 0.");
            }

            // 4) POY tidak boleh dikonversi dari bobbin — pesan sudah baku.
            if ($transaksi->jenis_masuk === JenisMasuk::PoyIpci) {
                $this->konversiService->validasiBeratPoy(
                    (float) $detail->berat_kg,
                    $detail->jumlah_bobbin !== null ? (int) $detail->jumlah_bobbin : null,
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helper kontekstual form & tampilan
    |--------------------------------------------------------------------------
    */

    /**
     * Gross per box aktual (kg) — null bila box tidak diisi / nol.
     *
     * Dipakai validasi peringatan DTY: gross per box dibandingkan
     * dengan rentang grade, BUKAN diblokir, karena bobbin sisa/putus sah.
     */
    public static function grossPerBox(float $grossKg, ?float $jumlahBox): ?float
    {
        if ($jumlahBox === null || $jumlahBox <= 0) {
            return null;
        }

        return round($grossKg / $jumlahBox, 2);
    }

    /**
     * Rentang toleransi gross per box per grade dari config/wms.php.
     *
     * Grade yang tidak dikonfigurasi (mis. C / scrap) mengembalikan null —
     * validasi dilewati karena bobbin sisa/putus sah.
     *
     * @return array{min: float, max: float}|null
     */
    public static function rentangGrossGrade(?string $kodeGrade): ?array
    {
        if ($kodeGrade === null) {
            return null;
        }

        $rentang = config('wms.satuan.dty_gross_box_per_grade.'.$kodeGrade);

        if (! is_array($rentang)) {
            return null;
        }

        return [
            'min' => (float) $rentang['min'],
            'max' => (float) $rentang['max'],
        ];
    }

    /**
     * Apakah gross per box berada di luar toleransi grade (peringatan saja).
     */
    public static function grossDiLuarToleransi(float $grossPerBox, ?string $kodeGrade): bool
    {
        $rentang = self::rentangGrossGrade($kodeGrade);

        if ($rentang === null) {
            return false;
        }

        return $grossPerBox < $rentang['min'] || $grossPerBox > $rentang['max'];
    }

    /**
     * Nilai auto-fill DTY untuk endpoint pratinjau form (box -> bobbin/netto/gross).
     *
     * @return array{bobbin: int, netto_kg: float, gross_kg: float}
     */
    public function autoFillDty(float $jumlahBox): array
    {
        return $this->konversiService->autoFillDty($jumlahBox);
    }

    /**
     * Daftar rentang gross per grade sebagai JSON untuk JavaScript form DTY.
     */
    public static function petaToleransiGrade(): array
    {
        $peta = [];

        foreach ((array) config('wms.satuan.dty_gross_box_per_grade', []) as $kode => $rentang) {
            $peta[$kode] = [
                'min' => (float) $rentang['min'],
                'max' => (float) $rentang['max'],
            ];
        }

        return $peta;
    }

    /**
     * Total berat kg seluruh detail transaksi.
     */
    public static function totalKg(TransaksiMasuk $transaksi): float
    {
        return (float) $transaksi->detail->sum(fn (TransaksiMasukDetail $detail) => (float) $detail->berat_kg);
    }

    /**
     * Tanggal bisnis transaksi sudah jatuh pada hari kerja (Senin–Sabtu)?
     */
    public function tanggalHariKerja(CarbonInterface $tanggal): bool
    {
        return $this->alokasiBufferService->isHariKerja($tanggal);
    }

    /**
     * Jumlah box dari detail — kolom stok_barang.jumlah_box diisi dari sini.
     *
     * Box tidak punya kolom sendiri pada `transaksi_masuk_detail`; yang
     * disimpan adalah `jumlah_kemasan` + `satuan_kemasan_id`. Box hanya
     * dihitung bila satuannya benar-benar BOX (dialokasikan ke stok POY/DTY
     * sebagai atribut pelengkap, bukan akuntansi).
     */
    public function jumlahBoxDariDetail(TransaksiMasukDetail $detail): ?int
    {
        $satuan = $detail->satuanKemasan;

        if ($satuan === null || strtoupper((string) $satuan->kode) !== self::SATUAN_BOX) {
            return null;
        }

        return $detail->jumlah_kemasan !== null ? (int) round((float) $detail->jumlah_kemasan) : null;
    }
}
