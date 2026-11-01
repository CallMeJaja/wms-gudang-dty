<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\KeluarMasukExport;
use App\Http\Controllers\Controller;
use App\Models\KategoriBarang;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Laporan keluar-masuk (PRD §6.1) — sumber `mutasi_stok`.
 */
class KeluarMasukController extends Controller
{
    public function __construct(private readonly LaporanService $laporan) {}

    public function index(Request $request): View
    {
        $filter = $this->filter($request);
        $baris = $this->laporan->barisKeluarMasuk($filter);

        return view('laporan.keluar-masuk.index', [
            'filter' => $filter,
            'baris' => $baris,
            'total' => $this->laporan->totalKeluarMasuk($baris),
            'daftarKategori' => KategoriBarang::orderBy('kode')->get(),
            'petaArus' => LaporanService::petaArus(),
        ]);
    }

    public function excel(Request $request): BinaryFileResponse
    {
        return Excel::download(new KeluarMasukExport($this->filter($request)), 'laporan-keluar-masuk.xlsx');
    }

    public function pdf(Request $request): Response
    {
        $filter = $this->filter($request);
        $baris = $this->laporan->barisKeluarMasuk($filter);

        return Pdf::loadView('laporan.keluar-masuk.pdf', [
            'filter' => $filter,
            'baris' => $baris,
            'total' => $this->laporan->totalKeluarMasuk($baris),
        ])->download('laporan-keluar-masuk.pdf');
    }

    /**
     * @return array{dari: string, sampai: string, kategori: ?string, arus: ?string, merge: ?string}
     */
    private function filter(Request $request): array
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
}
