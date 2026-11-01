<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

/**
 * Data awal konversi satuan untuk perhitungan stok.
 *
 * Aturan:
 * - POY tidak memiliki konversi bobbin ke kg karena saldo POY wajib
 *   berasal dari timbangan. Karena itu tidak ada baris konversi untuk POY.
 * - DTY boleh dikonversi dari box ke bobbin lalu ke kg untuk validasi silang.
 * - Pallet hanya dipakai sebagai ambang visual, bukan konversi akuntansi.
 */
class KonversiSatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuan = Satuan::pluck('id', 'kode');
        $dty = Barang::where('kode_barang', 'DTY-150-48-1')->first();

        if (! $dty) {
            return;
        }

        $boxBobbin = (float) config('wms.satuan.dty_box_bobbin');
        $bobbinKg = (float) config('wms.satuan.dty_bobbin_kg');
        $boxNetto = (float) config('wms.satuan.dty_box_netto_kg');

        $daftar = [
            // 1 box DTY = 6 bobbin
            ['dari' => 'BOX', 'ke' => 'BOBBIN', 'faktor' => $boxBobbin],
            // 1 bobbin DTY = 5,5 kg netto
            ['dari' => 'BOBBIN', 'ke' => 'KG', 'faktor' => $bobbinKg],
            // 1 box DTY = 33 kg netto
            ['dari' => 'BOX', 'ke' => 'KG', 'faktor' => $boxNetto],
        ];

        foreach ($daftar as $k) {
            KonversiSatuan::updateOrCreate(
                [
                    'barang_id' => $dty->id,
                    'dari_satuan_id' => $satuan[$k['dari']],
                    'ke_satuan_id' => $satuan[$k['ke']],
                ],
                [
                    'faktor' => $k['faktor'],
                    'is_default' => true,
                ]
            );
        }
    }
}
