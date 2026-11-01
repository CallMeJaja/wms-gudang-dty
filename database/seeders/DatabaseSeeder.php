<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan seeder mengikuti ketergantungan FK.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,            // users (dibutuhkan created_by)
            KategoriSatuanSeeder::class,  // kategori_barang, satuan
            GradeSeeder::class,           // grade (butuh kategori)
            BarangSeeder::class,          // barang (butuh kategori + satuan)
            PartnerSeeder::class,         // supplier_customer
            KonversiSatuanSeeder::class,  // konversi_satuan (butuh barang + satuan)
            NomorDokumenSeeder::class,    // nomor_dokumen periode berjalan
        ]);
    }
}
