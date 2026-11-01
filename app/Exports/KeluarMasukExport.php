<?php

namespace App\Exports;

use App\Services\LaporanService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export Excel laporan keluar-masuk (§6.1).
 */
class KeluarMasukExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  array{dari?: ?string, sampai?: ?string, kategori?: ?string, arus?: ?string, merge?: ?string}  $filter
     */
    public function __construct(private readonly array $filter) {}

    public function collection(): Collection
    {
        return app(LaporanService::class)->barisKeluarMasuk($this->filter);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Tanggal', 'Barang', 'Merge', 'Grade', 'Jenis', 'Pos', 'Ref Nomor', 'Masuk (Kg)', 'Keluar (Kg)'];
    }

    /**
     * @param  mixed  $baris
     * @return array<int, mixed>
     */
    public function map($baris): array
    {
        return [
            $baris->tanggal?->format('Y-m-d'),
            $baris->barang?->kode_barang,
            $baris->merge,
            $baris->grade?->kode,
            $baris->jenis_transaksi instanceof \BackedEnum ? $baris->jenis_transaksi->value : (string) $baris->jenis_transaksi,
            $baris->pos instanceof \BackedEnum ? $baris->pos->value : (string) $baris->pos,
            $baris->ref_nomor,
            (float) $baris->masuk_qty,
            (float) $baris->keluar_qty,
        ];
    }
}
