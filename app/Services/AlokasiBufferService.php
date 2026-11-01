<?php

namespace App\Services;

use App\Enums\StatusDokumen;
use App\Models\ActivityLog;
use App\Models\AlokasiBuffer;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Alokasi buffer Minggu untuk penerimaan POY.
 *
 * Aturan yang dijaga:
 *  1. Hanya dijalankan saat penerimaan berstatus POSTED (DRAFT tidak
 *     mengubah stok maupun buffer).
 *  2. Hanya untuk barang `is_merge_minggu = true`. Merge lain seluruhnya
 *     AKTIF dan TIDAK membuat baris `alokasi_buffer`.
 *  3. Hanya pada hari kerja Senin–Sabtu. Hari Minggu tidak ada alokasi
 *     (buffer justru dirilis ke AKTIF).
 *  4. Pasokan hari itu < `ambang_poy.lewati_buffer_jika_kurang_dari` pallet
 *     -> buffer DILEWATI (tidak dipotong proporsional). Seluruhnya AKTIF,
 *     tetap dicatat `alokasi_buffer.dilewati = true`.
 *  5. Pasokan >= ambang -> sisihkan 1 pallet (`pallet.poy_kg`) ke BUFFER,
 *     sisanya AKTIF — tetapi hanya selama total buffer gudang lintas
 *     SEMUA merge Minggu masih < `ambang_poy.target_buffer_pallet`
 *     pallet (kapasitas TOTAL GUDANG, bukan per merge).
 *  6. Tidak pernah menyisihkan lebih dari sisa kuota. Bila sisa kuota
 *     lebih kecil dari satu pallet, hanya sebesar sisa kuota itulah
 *     yang disisihkan (bukan satu pallet penuh).
 *
 * Prasyarat: kg penerimaan detail WAJIB sudah ditambahkan ke pos AKTIF
 * oleh pemanggil (service transaksi) SEBELUM service ini dipanggil.
 * Service ini hanya MEMINDAHKAN porsi dari AKTIF ke BUFFER, sehingga
 * total stok (aktif + buffer) tidak pernah berubah di sini.
 */
class AlokasiBufferService
{
    private const MODUL = 'ALOKASI_BUFFER';

    public function __construct(private readonly StokService $stokService) {}

    /**
     * Proses alokasi buffer untuk satu penerimaan POY yang baru POSTED.
     *
     * @param  Collection<int, TransaksiMasukDetail>  $detailList
     */
    public function prosesAlokasiUntukPenerimaan(TransaksiMasuk $transaksi, Collection $detailList): void
    {
        if ($transaksi->status !== StatusDokumen::Posted) {
            return;
        }

        // Hari Minggu: tidak ada alokasi (buffer dirilis, bukan diisi).
        if (! $this->isHariKerja($transaksi->tanggal)) {
            return;
        }

        $detailPoy = $this->detailPoyMergeMinggu($detailList);

        if ($detailPoy->isEmpty()) {
            return;
        }

        $pasokanKg = (float) $this->detailPoyHariItu($transaksi)->sum('berat_kg');
        $palletHariItu = $this->kgKePallet($pasokanKg);
        $ambangPallet = (int) config('wms.ambang_poy.lewati_buffer_jika_kurang_dari');

        DB::transaction(function () use ($transaksi, $detailPoy, $pasokanKg, $palletHariItu, $ambangPallet): void {
            foreach ($detailPoy as $detail) {
                $this->alokasiSatuDetail($transaksi, $detail, $pasokanKg, $palletHariItu, $ambangPallet);
            }
        });
    }

    /**
     * Info status kesiapan buffer Minggu untuk widget dashboard.
     *
     * Level yang dipakai:
     *  - Siap      : buffer >= target (>= target pallet)
     *  - Terlambat : buffer < target DAN hari Minggu <= 1 hari lagi
     *  - Belum Siap: selain itu
     *
     * @param  CarbonInterface|null  $saat  waktu acuan (default: now()), memudahkan test.
     */
    public function statusBuffer(?CarbonInterface $saat = null): StatusBufferMinggu
    {
        $saat = $this->diZonaAplikasi($saat ?? now());

        $targetPallet = (int) config('wms.ambang_poy.target_buffer_pallet');
        $kgPerPallet = (float) config('wms.pallet.poy_kg');
        $targetKg = $targetPallet * $kgPerPallet;

        $bufferKg = round($this->stokService->totalBufferMinggu(), 2);
        $sisaKuotaKg = max(0.0, round($targetKg - $bufferKg, 2));
        $sisaHariKerja = $this->sisaHariKerjaMenujuMinggu($saat);

        [$level, $label, $warna, $pesan] = match (true) {
            $bufferKg >= $targetKg => [
                StatusBufferMinggu::SIAP,
                'Siap',
                'hijau',
                'Buffer hari Minggu terpenuhi.',
            ],
            $sisaHariKerja <= 1 => [
                StatusBufferMinggu::TERLAMBAT,
                'Terlambat',
                'merah',
                'Buffer Minggu tidak akan terpenuhi — eskalasi ke manajer.',
            ],
            default => [
                StatusBufferMinggu::BELUM_SIAP,
                'Belum Siap',
                'kuning',
                sprintf(
                    'Buffer Minggu baru %s dari %d pallet. Sisa %d hari kerja untuk mengejar.',
                    number_format($this->kgKePallet($bufferKg), 2, ',', '.'),
                    $targetPallet,
                    $sisaHariKerja,
                ),
            ],
        };

        return new StatusBufferMinggu(
            bufferKg: $bufferKg,
            bufferPallet: round($this->kgKePallet($bufferKg), 2),
            targetPallet: $targetPallet,
            targetKg: $targetKg,
            sisaKuotaKg: $sisaKuotaKg,
            sisaHariKerja: $sisaHariKerja,
            level: $level,
            label: $label,
            pesan: $pesan,
            warna: $warna,
        );
    }

    /**
     * Jumlah hari kerja (Senin–Sabtu) tersisa sebelum hari Minggu terdekat.
     *
     * Senin = 6 hari lagi, Jumat = 2, Sabtu = 1, Minggu = 0.
     *
     * Hari dinilai pada zona waktu aplikasi (WIB), bukan UTC, karena
     * shift 3 melintasi tengah malam. Nilai hari memakai ISO-8601
     * (format 'N': 1 = Senin ... 7 = Minggu) yang eksplisit dan tidak
     * bergantung pada konstanta/dayOfWeek milik library tanggal.
     */
    public function sisaHariKerjaMenujuMinggu(CarbonInterface $saat): int
    {
        $hariIso = (int) $this->diZonaAplikasi($saat)->format('N'); // 1 = Senin ... 7 = Minggu

        // Hari kerja tersisa sampai Minggu: Senin 6, Selasa 5, ..., Sabtu 1, Minggu 0.
        return max(0, $this->jumlahHariKerja() + 1 - $hariIso);
    }

    /**
     * True bila tanggal jatuh pada hari kerja operasional (Senin–Sabtu).
     */
    public function isHariKerja(CarbonInterface $tanggal): bool
    {
        return $this->sisaHariKerjaMenujuMinggu($tanggal) > 0;
    }

    /**
     * Normalisasi waktu acuan ke zona waktu aplikasi agar penentuan hari benar.
     */
    private function diZonaAplikasi(CarbonInterface $saat): CarbonInterface
    {
        return $saat->copy()->setTimezone((string) config('app.timezone', 'Asia/Jakarta'));
    }

    /**
     * Panjang siklus buffer (jumlah hari kerja Senin–Sabtu) dari config.
     */
    private function jumlahHariKerja(): int
    {
        return (int) config('wms.operasional.buffer_hari');
    }

    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    /**
     * Alokasi untuk satu baris detail (satu barang+merge+grade).
     */
    private function alokasiSatuDetail(
        TransaksiMasuk $transaksi,
        TransaksiMasukDetail $detail,
        float $pasokanKg,
        float $palletHariItu,
        int $ambangPallet,
    ): void {
        $barang = $detail->barang;
        $tanggal = $transaksi->tanggal;

        $referensi = $this->barisAlokasi($barang, $detail->merge, $tanggal);

        // Sudah pernah diproses untuk (barang, merge, tanggal) ini -> jangan dobel.
        if ($referensi !== null) {
            return;
        }

        // Sisa kuota dihitung ULANG per detail dari saldo aktual gudang,
        // sehingga alokasi detail sebelumnya di transaksi ini ikut terhitung.
        $sisaKuotaKg = $this->sisaKuotaBufferKg();

        $keteranganPasokan = sprintf(
            'Pasokan %s pallet (%s kg) — ambang %d pallet.',
            number_format($palletHariItu, 2, ',', '.'),
            number_format($pasokanKg, 2, ',', '.'),
            $ambangPallet,
        );

        if ($palletHariItu < $ambangPallet) {
            $this->catatBarisAlokasi(
                $transaksi, $detail, 0.0, true,
                'Buffer dilewati: '.$keteranganPasokan
            );

            return;
        }

        if ($sisaKuotaKg <= 0) {
            $this->catatBarisAlokasi(
                $transaksi, $detail, 0.0, false,
                'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. '
                .$keteranganPasokan
            );

            return;
        }

        $porsi = round(min((float) config('wms.pallet.poy_kg'), $sisaKuotaKg), 2);

        $this->stokService->alokasiKeBuffer(
            barang: $barang,
            merge: $detail->merge,
            gradeId: (int) $detail->grade_id,
            porsi: $porsi,
            refNomor: (string) $transaksi->nomor_transaksi,
            userId: $transaksi->created_by !== null ? (int) $transaksi->created_by : null,
            tanggal: $tanggal,
        );

        $this->catatBarisAlokasi(
            $transaksi, $detail, $porsi, false,
            sprintf('%s kg disisihkan ke buffer. %s', number_format($porsi, 2, ',', '.'), $keteranganPasokan)
        );

        ActivityLog::catat(
            'RELEASE_BUFFER',
            self::MODUL,
            $transaksi->id,
            sprintf(
                'Alokasi buffer %s kg (barang %s, merge %s) dari penerimaan %s.',
                number_format($porsi, 2, ',', '.'),
                $barang->kode_barang,
                $detail->merge,
                $transaksi->nomor_transaksi,
            )
        );
    }

    /**
     * Cari baris alokasi yang sudah ada untuk (barang, merge, tanggal) — UNIQUE DB.
     */
    private function barisAlokasi(Barang $barang, string $merge, CarbonInterface|string $tanggal): ?AlokasiBuffer
    {
        return AlokasiBuffer::query()
            ->where('barang_id', $barang->id)
            ->where('merge', $merge)
            ->whereDate('tanggal', $tanggal)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Catat / perbarui baris jejak kebijakan buffer.
     *
     * UNIQUE (barang_id, merge, tanggal) dijaga dengan updateOrCreate di
     * dalam transaction + row lock lebih dulu.
     */
    private function catatBarisAlokasi(
        TransaksiMasuk $transaksi,
        TransaksiMasukDetail $detail,
        float $jumlahKg,
        bool $dilewati,
        string $keterangan,
    ): AlokasiBuffer {
        return AlokasiBuffer::updateOrCreate(
            [
                'barang_id' => $detail->barang_id,
                'merge' => $detail->merge,
                'tanggal' => $transaksi->tanggal,
            ],
            [
                'grade_id' => $detail->grade_id,
                'jumlah_kg' => $jumlahKg,
                'sumber_transaksi_masuk_id' => $transaksi->id,
                'dilewati' => $dilewati,
                'keterangan' => $keterangan,
                'created_by' => $transaksi->created_by,
            ]
        );
    }

    /**
     * Sisa kuota buffer gudang dalam kg (target lintas merge - saldo buffer).
     */
    private function sisaKuotaBufferKg(): float
    {
        $targetKg = (int) config('wms.ambang_poy.target_buffer_pallet')
            * (float) config('wms.pallet.poy_kg');

        return max(0.0, round($targetKg - $this->stokService->totalBufferMinggu(), 2));
    }

    /**
     * Ambil detail yang relevan untuk alokasi buffer:
     * barang kategori POY DAN ditandai is_merge_minggu = true.
     *
     * @param  Collection<int, TransaksiMasukDetail>  $detailList
     * @return Collection<int, TransaksiMasukDetail>
     */
    private function detailPoyMergeMinggu(Collection $detailList): Collection
    {
        return $detailList
            ->filter(function (TransaksiMasukDetail $detail): bool {
                $barang = $detail->barang;

                return $barang instanceof Barang
                    && $barang->kategori?->kode === KategoriBarang::POY
                    && (bool) $barang->is_merge_minggu;
            })
            // Merge yang datang lebih dulu mengisi kuota lebih dulu.
            ->sortBy(fn (TransaksiMasukDetail $detail): string => $detail->merge)
            ->values();
    }

    /**
     * Seluruh detail POY pada tanggal penerimaan ini, semua transaksi POSTED.
     *
     * Dasar penilaian "pasokan hari itu" (bukan per baris detail, agar
     * satu hari yang dipenuhi beberapa surat jalan tetap dihitung utuh).
     *
     * @return Collection<int, TransaksiMasukDetail>
     */
    private function detailPoyHariItu(TransaksiMasuk $transaksi): Collection
    {
        return TransaksiMasukDetail::query()
            ->whereHas('transaksi', fn ($q) => $q
                ->whereDate('tanggal', $transaksi->tanggal)
                ->mempengaruhiStok())
            ->whereHas('barang', fn ($q) => $q
                ->whereHas('kategori', fn ($k) => $k->where('kode', KategoriBarang::POY)))
            ->get();
    }

    /**
     * Kg -> pallet memakai acuan POY untuk ambang visual.
     */
    private function kgKePallet(float $kg): float
    {
        $kgPerPallet = (float) config('wms.pallet.poy_kg');

        return $kgPerPallet > 0 ? $kg / $kgPerPallet : 0.0;
    }
}
