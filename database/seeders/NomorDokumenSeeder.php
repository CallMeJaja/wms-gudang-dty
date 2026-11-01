<?php

namespace Database\Seeders;

use App\Models\NomorDokumen;
use Illuminate\Database\Seeder;

/**
 * Baris awal nomor_dokumen untuk periode berjalan.
 *
 * Baris wajib ada untuk semua jenis dokumen agar penomoran atomik
 * dengan lockForUpdate() selalu punya baris yang bisa dikunci.
 */
class NomorDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [
            NomorDokumen::SURAT_JALAN_DTY,
            NomorDokumen::GATE_PASS,
            NomorDokumen::TRX_MASUK,
            NomorDokumen::TRX_KELUAR,
        ];

        foreach ($jenis as $j) {
            NomorDokumen::firstOrCreate(
                [
                    'jenis' => $j,
                    'periode' => NomorDokumen::periodeUntuk($j),
                ],
                ['last_number' => 0]
            );
        }
    }
}
