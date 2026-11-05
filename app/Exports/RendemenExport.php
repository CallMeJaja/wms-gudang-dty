<?php

namespace App\Exports;

use App\Services\LaporanService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Export Excel rendemen POY masuk vs DTY keluar.
 */
class RendemenExport implements FromCollection, WithHeadings
{
    /**
     * @param  array{dari: string, sampai: string}  $filter
     */
    public function __construct(private readonly array $filter) {}

    public function collection(): Collection
    {
        $hasil = app(LaporanService::class)->rendemen($this->filter['dari'], $this->filter['sampai']);

        return collect([
            ['POY Masuk (kg)', $hasil['totalPoy']],
            ['POY Masuk (ton)', $hasil['totalPoy'] / 1000],
            ['DTY Keluar (kg)', $hasil['totalDty']],
            ['DTY Keluar (ton)', $hasil['totalDty'] / 1000],
            ['Rendemen (%)', $hasil['rendemen'] ?? '-'],
            ['Waste / Selisih (kg)', $hasil['wasteKg']],
            ['Waste / Selisih (ton)', $hasil['wasteKg'] / 1000],
            ['Waste (%)', $hasil['waste'] ?? '-'],
            ['Target Bulanan (kg)', $hasil['targetKg']],
            ['Target Bulanan (ton)', $hasil['targetKg'] / 1000],
            ['Pencapaian Target (%)', $hasil['pencapaianTarget'] ?? '-'],
            ['Selisih Target (kg)', $hasil['selisihTargetKg']],
            ['Status', $hasil['surplus'] ? 'Surplus / data tidak valid' : 'Normal'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Metrik', 'Nilai'];
    }
}
