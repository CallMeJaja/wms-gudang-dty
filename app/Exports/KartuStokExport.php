<?php

namespace App\Exports;

use App\Models\Barang;
use App\Services\LaporanService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Export Excel kartu stok (§6.2) — satu baris per mutasi + saldo berjalan.
 */
class KartuStokExport implements FromCollection, WithHeadings
{
    /**
     * @param  array{dari: string, sampai: string, barang_id: ?int, merge: string, grade_id: ?int, pos: string}  $filter
     */
    public function __construct(private readonly array $filter) {}

    public function collection(): Collection
    {
        if ($this->filter['barang_id'] === null || $this->filter['merge'] === '') {
            return collect();
        }

        $layanan = app(LaporanService::class);

        $barang = Barang::find($this->filter['barang_id']);

        $hasil = $layanan->kartuStok(
            $this->filter['barang_id'], $this->filter['merge'], $this->filter['grade_id'],
            $this->filter['pos'] ?: null, $this->filter['dari'], $this->filter['sampai'],
        );

        $baris = collect([[
            'SALDO AWAL ('.$this->filter['dari'].')',
            $barang?->kode_barang, $this->filter['merge'], '', '', null, null, $hasil['saldoAwal'],
        ]]);

        foreach ($hasil['baris'] as $mutasi) {
            $baris->push([
                $mutasi->tanggal?->format('Y-m-d'),
                $barang?->kode_barang,
                $mutasi->merge,
                $mutasi->pos instanceof \BackedEnum ? $mutasi->pos->value : (string) $mutasi->pos,
                $mutasi->ref_nomor,
                (float) $mutasi->masuk_qty,
                (float) $mutasi->keluar_qty,
                (float) $mutasi->getAttribute('saldo_berjalan'),
            ]);
        }

        return $baris;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Tanggal', 'Barang', 'Merge', 'Pos', 'Ref / Keterangan', 'Masuk (Kg)', 'Keluar (Kg)', 'Saldo (Kg)'];
    }
}
