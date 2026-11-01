<?php

namespace App\Exports;

use App\Services\LaporanService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Export Excel rendemen POY vs DTY (§6.3) — total + per merge POY asal.
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

        $baris = $hasil['perMerge']->map(fn (array $r): array => [
            $r['merge'], $r['poy'], $r['dty'],
            $r['rendemen'] ?? '-', $r['waste'] ?? '-',
        ])->values();

        $baris->push([
            'TOTAL', $hasil['totalPoy'], $hasil['totalDty'],
            $hasil['rendemen'] ?? '-', $hasil['waste'] ?? '-',
        ]);

        return $baris;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Merge POY Asal', 'POY Keluar (Kg)', 'DTY Masuk (Kg)', 'Rendemen (%)', 'Waste (%)'];
    }
}
