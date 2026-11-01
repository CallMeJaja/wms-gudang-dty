<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\RendemenExport;
use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Rekonsiliasi POY vs DTY (PRD §6.3) — indikator efisiensi massa.
 */
class RendemenController extends Controller
{
    public function __construct(private readonly LaporanService $laporan) {}

    public function index(Request $request): View
    {
        $filter = $this->filter($request);

        return view('laporan.rendemen.index', array_merge($filter, [
            'hasil' => $this->laporan->rendemen($filter['dari'], $filter['sampai']),
            'disclaimer' => (string) config('wms.rendemen.disclaimer'),
        ]));
    }

    public function excel(Request $request): BinaryFileResponse
    {
        return Excel::download(new RendemenExport($this->filter($request)), 'rendemen-poy-dty.xlsx');
    }

    public function pdf(Request $request): Response
    {
        $filter = $this->filter($request);

        return Pdf::loadView('laporan.rendemen.pdf', array_merge($filter, [
            'hasil' => $this->laporan->rendemen($filter['dari'], $filter['sampai']),
            'disclaimer' => (string) config('wms.rendemen.disclaimer'),
        ]))->download('rendemen-poy-dty.pdf');
    }

    /**
     * @return array{dari: string, sampai: string}
     */
    private function filter(Request $request): array
    {
        $default = LaporanService::defaultPeriode();

        return [
            'dari' => $request->query('dari', $default['dari']),
            'sampai' => $request->query('sampai', $default['sampai']),
        ];
    }
}
