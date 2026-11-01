<?php

namespace App\Http\Controllers\Admin;

use App\Exports\KartuStokExport;
use App\Exports\KeluarMasukExport;
use App\Exports\RendemenExport;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Endpoint export laporan milik panel Filament.
 *
 * Logika filter, export, dan view PDF dipertahankan agar hasil unduhan
 * tetap identik saat halaman Filament beralih ke route `admin.laporan.*`.
 */
class LaporanExportController extends Controller
{
    public function __construct(private readonly LaporanService $laporan) {}

    public function keluarMasukExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(new KeluarMasukExport($this->filterKeluarMasuk($request)), 'laporan-keluar-masuk.xlsx');
    }

    public function keluarMasukPdf(Request $request): Response
    {
        $filter = $this->filterKeluarMasuk($request);
        $baris = $this->laporan->barisKeluarMasuk($filter);

        return Pdf::loadView('laporan.keluar-masuk.pdf', [
            'filter' => $filter,
            'baris' => $baris,
            'total' => $this->laporan->totalKeluarMasuk($baris),
        ])->download('laporan-keluar-masuk.pdf');
    }

    public function kartuStokExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(new KartuStokExport($this->filterKartuStok($request)), 'kartu-stok.xlsx');
    }

    public function kartuStokPdf(Request $request): Response
    {
        return Pdf::loadView('laporan.kartu-stok.pdf', array_merge(
            $this->filterKartuStok($request),
            $this->dataHasilKartuStok($this->filterKartuStok($request)),
        ))->download('kartu-stok.pdf');
    }

    public function rendemenExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(new RendemenExport($this->filterRendemen($request)), 'rendemen-poy-dty.xlsx');
    }

    public function rendemenPdf(Request $request): Response
    {
        $filter = $this->filterRendemen($request);

        return Pdf::loadView('laporan.rendemen.pdf', array_merge($filter, [
            'hasil' => $this->laporan->rendemen($filter['dari'], $filter['sampai']),
            'disclaimer' => trim(config('wms.rendemen.disclaimer_ringkas').' '.config('wms.rendemen.disclaimer_lengkap')),
        ]))->download('rendemen-poy-dty.pdf');
    }

    /**
     * @return array{dari: string, sampai: string, kategori: ?string, arus: ?string, merge: ?string}
     */
    private function filterKeluarMasuk(Request $request): array
    {
        $default = LaporanService::defaultPeriode();

        $arus = $request->query('arus');

        return [
            'dari' => $request->query('dari', $default['dari']),
            'sampai' => $request->query('sampai', $default['sampai']),
            'kategori' => $request->query('kategori') ?: null,
            'arus' => in_array($arus, ['MASUK', 'KELUAR', 'VOID'], true) ? $arus : null,
            'merge' => trim((string) $request->query('merge', '')) ?: null,
        ];
    }

    /**
     * @return array{dari: string, sampai: string, barang_id: ?int, merge: string, grade_id: ?int, pos: string}
     */
    private function filterKartuStok(Request $request): array
    {
        $default = LaporanService::defaultPeriode();
        $pos = $request->query('pos', '');

        return [
            'dari' => $request->query('dari', $default['dari']),
            'sampai' => $request->query('sampai', $default['sampai']),
            'barang_id' => $request->query('barang_id') !== null && $request->query('barang_id') !== ''
                ? (int) $request->query('barang_id') : null,
            'merge' => trim((string) $request->query('merge', '')),
            'grade_id' => $request->query('grade_id') !== null && $request->query('grade_id') !== ''
                ? (int) $request->query('grade_id') : null,
            'pos' => in_array($pos, ['AKTIF', 'BUFFER'], true) ? $pos : '',
        ];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    private function dataHasilKartuStok(array $filter): array
    {
        if ($filter['barang_id'] === null || $filter['merge'] === '') {
            return ['saldoAwal' => 0.0, 'baris' => collect(), 'barang' => null];
        }

        return array_merge(
            $this->laporan->kartuStok(
                $filter['barang_id'], $filter['merge'], $filter['grade_id'],
                $filter['pos'] ?: null, $filter['dari'], $filter['sampai'],
            ),
            ['barang' => Barang::find($filter['barang_id'])],
        );
    }

    /**
     * @return array{dari: string, sampai: string}
     */
    private function filterRendemen(Request $request): array
    {
        $default = LaporanService::defaultPeriode();

        return [
            'dari' => $request->query('dari', $default['dari']),
            'sampai' => $request->query('sampai', $default['sampai']),
        ];
    }
}
