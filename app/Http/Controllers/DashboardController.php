<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Services\AlokasiBufferService;
use App\Services\RilisBufferService;
use App\Services\StatusBufferMinggu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard operasional (F4, PRD §5.2.3–§5.2.4).
 *
 * A. Kecukupan Operasional Harian dari STOK AKTIF seluruh merge.
 * B. Kesiapan Buffer Minggu dari STOK CADANGAN merge Minggu.
 * Semua angka visual dari config('wms...') — tidak ada hardcode.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly AlokasiBufferService $buffer,
        private readonly RilisBufferService $rilis,
    ) {}

    public function index(): View
    {
        $aktifKg = (float) StokBarang::query()
            ->whereHas('barang', fn ($q) => $q->whereHas(
                'kategori', fn ($k) => $k->where('kode', KategoriBarang::POY)
            ))
            ->sum('stok_aktif');

        $kgPerPallet = (float) config('wms.pallet.poy_kg');
        $minPallet = (int) config('wms.ambang_poy.min_stok_aktif_pallet');
        $minKg = $minPallet * $kgPerPallet;
        $aktifPallet = $kgPerPallet > 0 ? $aktifKg / $kgPerPallet : 0.0;
        $aktifAman = $aktifKg >= $minKg;

        $statusBuffer = $this->buffer->statusBuffer();

        return view('dashboard.index', [
            'aktifKg' => $aktifKg,
            'aktifPallet' => round($aktifPallet, 2),
            'aktifAman' => $aktifAman,
            'minPallet' => $minPallet,
            'minKg' => $minKg,
            'statusBuffer' => $statusBuffer,
            'progresBuffer' => $statusBuffer->targetPallet > 0
                ? round($statusBuffer->bufferPallet / $statusBuffer->targetPallet * 100, 1)
                : 0.0,
            'bolehRilis' => $this->rilis->bolehRilisOtomatis(now())
                && $statusBuffer->bufferKg > 0,
            'grafik' => $this->grafikHarian(),
            'ringkasan' => $this->ringkasanKategori(),
            'alert' => $this->daftarAlert($aktifAman, $statusBuffer),
        ]);
    }

    /**
     * Rilis manual buffer Minggu. Hanya hari Minggu, role admin/operator
     * (dijaga middleware), idempoten bila buffer 0.
     */
    public function rilis(Request $request): RedirectResponse
    {
        if (! $this->rilis->bolehRilisOtomatis(now())) {
            return back()->with('error', 'Rilis buffer hanya dapat dilakukan pada hari Minggu.');
        }

        $hasil = $this->rilis->rilis(now(), $request->user()->id);

        return back()->with('sukses', sprintf(
            'Buffer Minggu dirilis: %s kg (%d baris) ke stok aktif.',
            number_format($hasil['dirilis_kg'], 2, ',', '.'),
            $hasil['baris']
        ));
    }

    /**
     * Agregasi mutasi 30 hari terakhir per tanggal: masuk vs keluar
     * POY & DTY (4 garis).
     *
     * @return array{labels: string[], masukPoy: float[], keluarPoy: float[], masukDty: float[], keluarDty: float[]}
     */
    private function grafikHarian(): array
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
     * @return array<int, array{kode: string, aktifKg: float, bufferKg: float, totalKg: float}>
     */
    private function ringkasanKategori(): array
    {
        $hasil = [];

        foreach ([KategoriBarang::POY, KategoriBarang::DTY, KategoriBarang::PENDUKUNG] as $kode) {
            $q = StokBarang::query()->whereHas('barang', fn ($query) => $query->whereHas(
                'kategori', fn ($k) => $k->where('kode', $kode)
            ));

            $aktif = (float) (clone $q)->sum('stok_aktif');
            $buffer = (float) (clone $q)->sum('stok_buffer');

            $hasil[] = [
                'kode' => $kode,
                'aktifKg' => $aktif,
                'bufferKg' => $buffer,
                'totalKg' => $aktif + $buffer,
            ];
        }

        return $hasil;
    }

    /**
     * @return array<int, array{level: string, pesan: string}>
     */
    private function daftarAlert(bool $aktifAman, StatusBufferMinggu $statusBuffer): array
    {
        $alert = [];

        if (! $aktifAman) {
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
                $total = $barang->stok->sum(fn ($s): float => (float) $s->stok_aktif + (float) $s->stok_buffer);

                if ($total < (float) $barang->stok_minimum) {
                    $alert[] = [
                        'level' => 'warning',
                        'pesan' => sprintf(
                            'Stok %s (%s kg) di bawah minimum %s kg.',
                            $barang->kode_barang,
                            number_format($total, 2, ',', '.'),
                            number_format((float) $barang->stok_minimum, 2, ',', '.')
                        ),
                    ];
                }
            });

        return $alert;
    }
}
