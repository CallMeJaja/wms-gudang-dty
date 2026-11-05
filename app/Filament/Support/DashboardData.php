<?php

namespace App\Filament\Support;

use App\Enums\JenisMutasi;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Services\AlokasiBufferService;
use App\Services\StatusBufferMinggu;

/**
 * Sumber data dashboard Filament.
 *
 * Salinan baca-saja dari logika dashboard agar tampilan panel tidak
 * menduplikasi logika mutasi stok.
 */
final class DashboardData
{
    /**
     * @return array{kg: float, pallet: float, minPallet: int, minKg: float, aman: bool}
     */
    public static function stokAktifPoy(): array
    {
        $kg = (float) StokBarang::query()
            ->whereHas('barang', fn ($q) => $q->whereHas(
                'kategori',
                fn ($k) => $k->where('kode', KategoriBarang::POY)
            ))
            ->sum('stok_aktif');

        $kgPerPallet = (float) config('wms.pallet.poy_kg');
        $minPallet = (int) config('wms.ambang_poy.min_stok_aktif_pallet');
        $minKg = $minPallet * $kgPerPallet;

        return [
            'kg' => $kg,
            'pallet' => $kgPerPallet > 0 ? round($kg / $kgPerPallet, 2) : 0.0,
            'minPallet' => $minPallet,
            'minKg' => $minKg,
            'aman' => $kg >= $minKg,
        ];
    }

    public static function statusBuffer(): StatusBufferMinggu
    {
        return app(AlokasiBufferService::class)->statusBuffer();
    }

    /**
     * Jumlah draf transaksi yang masih menunggu posting, dipisah per arah.
     *
     * @return array{penerimaan: int, pengeluaran: int, total: int}
     */
    public static function drafMenungguPosting(): array
    {
        $penerimaan = TransaksiMasuk::query()
            ->where('status', StatusDokumen::Draft->value)
            ->count();

        $pengeluaran = TransaksiKeluar::query()
            ->where('status', StatusDokumen::Draft->value)
            ->count();

        return [
            'penerimaan' => $penerimaan,
            'pengeluaran' => $pengeluaran,
            'total' => $penerimaan + $pengeluaran,
        ];
    }

    /**
     * Agregasi mutasi 30 hari terakhir per tanggal: masuk vs keluar
     * POY & DTY (4 garis).
     *
     * @return array{labels: string[], masukPoy: float[], keluarPoy: float[], masukDty: float[], keluarDty: float[]}
     */
    public static function grafikHarian(): array
    {
        $akhir = now()->startOfDay();
        $awal = $akhir->copy()->subDays(29);

        $labels = $masukPoy = $keluarPoy = $masukDty = $keluarDty = [];

        for ($tgl = $awal->copy(); $tgl <= $akhir; $tgl->addDay()) {
            $labels[] = $tgl->locale('id')->translatedFormat('d M');
            $masukPoy[] = $keluarPoy[] = $masukDty[] = $keluarDty[] = 0.0;
        }

        $agregat = MutasiStok::query()
            ->periode($awal, $akhir->copy()->endOfDay())
            ->whereIn('jenis_transaksi', [JenisMutasi::Masuk->value, JenisMutasi::Keluar->value])
            ->with('barang.kategori')
            ->get()
            ->groupBy(fn (MutasiStok $m): string => $m->tanggal->format('Y-m-d').'|'.($m->barang->kategori->kode ?? '?'));

        for ($i = 0; $i < 30; $i++) {
            $kunci = $awal->copy()->addDays($i)->format('Y-m-d');

            foreach ([KategoriBarang::POY => 'Poy', KategoriBarang::DTY => 'Dty'] as $kode => $akhiran) {
                $grup = $agregat->get($kunci.'|'.$kode);

                if ($grup === null) {
                    continue;
                }

                ${'masuk'.$akhiran}[$i] = round($grup->sum('masuk_qty'), 2);
                ${'keluar'.$akhiran}[$i] = round($grup->sum('keluar_qty'), 2);
            }
        }

        return compact('labels', 'masukPoy', 'keluarPoy', 'masukDty', 'keluarDty');
    }

    /**
     * @return array<int, array{level: string, pesan: string}>
     */
    public static function alerts(): array
    {
        $stok = self::stokAktifPoy();
        $statusBuffer = self::statusBuffer();
        $alert = [];

        if (! $stok['aman']) {
            $alert[] = [
                'level' => 'danger',
                'pesan' => 'Stok aktif di bawah kebutuhan 1 hari kerja mesin — risiko stop produksi hari ini.',
            ];
        }

        if ($statusBuffer->level === StatusBufferMinggu::TERLAMBAT) {
            $alert[] = ['level' => 'danger', 'pesan' => $statusBuffer->pesan];
        } elseif ($statusBuffer->level === StatusBufferMinggu::BELUM_SIAP) {
            $alert[] = ['level' => 'warning', 'pesan' => $statusBuffer->pesan];
        }

        Barang::query()
            ->whereNotNull('stok_minimum')
            ->with('stok')
            ->get()
            ->each(function (Barang $barang) use (&$alert): void {
                $total = $barang->stok->sum(
                    fn ($s): float => (float) $s->stok_aktif + (float) $s->stok_buffer
                );

                if ($total < (float) $barang->stok_minimum) {
                    $alert[] = [
                        'level' => 'warning',
                        'pesan' => sprintf(
                            'Stok %s (%s kg) di bawah minimum %s kg.',
                            $barang->nama_barang,
                            number_format($total, 2, ',', '.'),
                            number_format((float) $barang->stok_minimum, 2, ',', '.')
                        ),
                    ];
                }
            });

        return $alert;
    }
}
