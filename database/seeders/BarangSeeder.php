<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

/**
 * Data awal master barang untuk kebutuhan operasional gudang.
 *
 * Catatan:
 * - POY dan DTY memakai satuan dasar KG agar saldo mengikuti timbangan.
 * - Item pendukung memakai PCS, ROLL, atau KG sesuai satuan dasarnya.
 * - Barang master bersifat generik; merge baru dibuat saat transaksi.
 *   Karena itu is_merge_minggu default-nya false dan penandaan merge
 *   Minggu dilakukan lewat CRUD master data.
 */
class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = KategoriBarang::pluck('id', 'kode');
        $satuan = Satuan::pluck('id', 'kode');

        $daftar = [
            // ---------------- POY ----------------
            [
                'kode_barang' => 'POY-250-48',
                'nama_barang' => 'POY 250/48',
                'kategori' => KategoriBarang::POY,
                'denier' => '250',
                'filament' => '48',
                'satuan' => 'KG',
                'stok_minimum' => null,
            ],

            // ---------------- DTY ----------------
            [
                'kode_barang' => 'DTY-150-48-1',
                'nama_barang' => 'DTY 150/48',
                'kategori' => KategoriBarang::DTY,
                'denier' => '150',
                'filament' => '48',
                'satuan' => 'KG',
                'stok_minimum' => null,
            ],

            // ---------------- Item Pendukung ----------------
            [
                'kode_barang' => 'PPR TUB DTY, BULLNOSE',
                'nama_barang' => 'PPR TUB DTY,BULLNOSE,212-222GM',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'PCS',
                'stok_minimum' => config('wms.ambang_pendukung.PAPER_TUBE'),
            ],
            [
                'kode_barang' => 'CARTON BOX DTY 675x444x278MM, KRAFT 200',
                'nama_barang' => 'CARTON BOX DTY 675X444X278MM,KRAFT 200',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'PCS',
                'stok_minimum' => config('wms.ambang_pendukung.CARTON_BOX'),
            ],
            [
                'kode_barang' => 'STRETCH FILM 12MC',
                'nama_barang' => 'STRETCH FILM 12MC',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'PCS',
                'stok_minimum' => null,
            ],
            [
                'kode_barang' => 'PALLET DTY SMALL BOX 1370x1150x140MM',
                'nama_barang' => 'PALLET DTY SMALL BOX,1370X1150X140MM',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'PCS',
                'stok_minimum' => null,
            ],
            [
                'kode_barang' => 'TAPE 250MX3IN, VISTAC TRANSPARENT',
                'nama_barang' => 'TAPE,250MX3IN,VISTAC,TRANSPARENT',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'PCS',
                'stok_minimum' => null,
            ],
            [
                'kode_barang' => 'DTY CONING OIL, TM368 BN',
                'nama_barang' => 'DTY CONING OIL,TM368 BN',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'KG',
                'stok_minimum' => config('wms.ambang_pendukung.CONING_OIL'),
            ],
            [
                'kode_barang' => 'DTY PLASTIC BAG HDPE VRGN (4%A) 36/42x55 CM',
                'nama_barang' => 'DTY PLASTIC BAG-HDPE VRGN (4%A) 36/42X55 CM',
                'kategori' => KategoriBarang::PENDUKUNG,
                'denier' => null,
                'filament' => null,
                'satuan' => 'KG',
                'stok_minimum' => null,
            ],
        ];

        foreach ($daftar as $item) {
            Barang::updateOrCreate(
                ['kode_barang' => $item['kode_barang']],
                [
                    'kategori_id' => $kategori[$item['kategori']],
                    'nama_barang' => $item['nama_barang'],
                    'denier' => $item['denier'],
                    'filament' => $item['filament'],
                    'satuan_dasar_id' => $satuan[$item['satuan']],
                    'stok_minimum' => $item['stok_minimum'],
                    'is_merge_minggu' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}
