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
 * - POY tetap wajib memakai berat timbangan; data konversi POY di bawah
 *   adalah master transkripsi dokumen, bukan jalur auto-fill berat.
 * - DTY boleh dikonversi dari box ke bobbin lalu ke kg untuk validasi silang.
 * - Pallet dipakai sebagai satuan kemasan pada dokumen.
 */
class KonversiSatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuan = Satuan::pluck('id', 'kode');
        $dty = Barang::where('kode_barang', 'DTY-150-48-1')->first();
        $poy = Barang::where('kode_barang', 'POY-250-48')->first();

        if (! $dty || ! $poy) {
            return;
        }

        $boxBobbin = (float) config('wms.satuan.dty_box_bobbin');
        $bobbinKg = (float) config('wms.satuan.dty_bobbin_kg');
        $boxNetto = (float) config('wms.satuan.dty_box_netto_kg');

        $daftar = [
            // DTY
            ['barang' => $dty, 'dari' => 'BOX', 'ke' => 'BOBBIN', 'faktor' => $boxBobbin],
            ['barang' => $dty, 'dari' => 'BOBBIN', 'ke' => 'KG', 'faktor' => $bobbinKg],
            ['barang' => $dty, 'dari' => 'BOX', 'ke' => 'KG', 'faktor' => $boxNetto],
            ['barang' => $dty, 'dari' => 'PALLET', 'ke' => 'BOX', 'faktor' => 30],

            // POY (transkripsi dokumen; berat timbangan tetap wajib)
            ['barang' => $poy, 'dari' => 'PALLET', 'ke' => 'BOX', 'faktor' => 1],
            ['barang' => $poy, 'dari' => 'BOX', 'ke' => 'BOBBIN', 'faktor' => 36],
            ['barang' => $poy, 'dari' => 'BOBBIN', 'ke' => 'KG', 'faktor' => 16],
            ['barang' => $poy, 'dari' => 'BOX', 'ke' => 'KG', 'faktor' => 576],
            ['barang' => $poy, 'dari' => 'PALLET', 'ke' => 'KG', 'faktor' => 576],
        ];

        foreach ($daftar as $k) {
            KonversiSatuan::updateOrCreate(
                [
                    'barang_id' => $k['barang']->id,
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
