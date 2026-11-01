<?php

namespace Database\Seeders;

use App\Models\SupplierCustomer;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partner = [
            [
                'kode' => 'IPCI',
                'nama' => 'IPCI - 300097905',
                'jenis' => 'BOTH',
                'kontak' => null,
                'alamat' => 'PT. Indorama Polychem Indonesia',
            ],
            [
                'kode' => 'STORE-IPCI',
                'nama' => 'Store IPCI',
                'jenis' => 'SUPPLIER',
                'kontak' => null,
                'alamat' => 'PT. Indorama Polychem Indonesia — Dept. STORE',
            ],
            [
                'kode' => 'AREA PRODUKSI',
                'nama' => 'Area Produksi DTY PEI',
                'jenis' => 'INTERNAL',
                'kontak' => null,
                'alamat' => 'Area Doffing / Transit Produksi',
            ],
        ];

        foreach ($partner as $data) {
            SupplierCustomer::updateOrCreate(
                ['kode' => $data['kode']],
                [
                    'nama' => $data['nama'],
                    'jenis' => $data['jenis'],
                    'kontak' => $data['kontak'],
                    'alamat' => $data['alamat'],
                ]
            );
        }
    }
}
