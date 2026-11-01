<?php

namespace Database\Seeders;

use App\Models\KategoriBarang;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

class KategoriSatuanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('wms.kategori_default') as $kategori) {
            KategoriBarang::updateOrCreate(
                ['kode' => $kategori['kode']],
                ['nama' => $kategori['nama']]
            );
        }

        foreach (config('wms.satuan_default') as $satuan) {
            Satuan::updateOrCreate(
                ['kode' => $satuan['kode']],
                ['nama' => $satuan['nama']]
            );
        }
    }
}
