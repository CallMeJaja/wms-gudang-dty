<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\KartuStokExport;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Grade;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Kartu stok (PRD §6.2) — saldo awal + saldo berjalan per pos.
 */
class KartuStokController extends Controller
{
    public function __construct(private readonly LaporanService $laporan) {}

    public function index(Request $request): View
    {
        $filter = $this->filter($request);

        $hasil = $filter['barang_id'] !== null && $filter['merge'] !== ''
            ? $this->laporan->kartuStok(
                $filter['barang_id'], $filter['merge'], $filter['grade_id'],
                $filter['pos'] ?: null, $filter['dari'], $filter['sampai'],
            )
            : ['saldoAwal' => 0.0, 'baris' => collect()];

        return view('laporan.kartu-stok.index', array_merge($filter, [
            'saldoAwal' => $hasil['saldoAwal'],
            'baris' => $hasil['baris'],
            'daftarBarang' => Barang::aktif()->with('kategori')->orderBy('kode_barang')->get(),
            'daftarGrade' => Grade::aktif()->orderBy('kode')->get(),
        ]));
    }

    public function excel(Request $request): BinaryFileResponse
    {
        return Excel::download(new KartuStokExport($this->filter($request)), 'kartu-stok.xlsx');
    }

    public function pdf(Request $request): Response
    {
        return Pdf::loadView('laporan.kartu-stok.pdf', array_merge(
            $this->filter($request),
            $this->dataHasil($this->filter($request)),
        ))->download('kartu-stok.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function dataHasil(array $filter): array
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
     * @return array{dari: string, sampai: string, barang_id: ?int, merge: string, grade_id: ?int, pos: string}
     */
    private function filter(Request $request): array
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
}
