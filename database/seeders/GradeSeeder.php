<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\KategoriBarang;
use Illuminate\Database\Seeder;

/**
 * Grade bersifat tertutup (A7) — nilainya berasal dari config/wms.php.
 *
 * CATATAN PENTING: grade DTY 'C' sengaja tetap di-seed dan aktif.
 * Data logbook tidak memuatnya, tetapi PIC mengonfirmasi scrap grade C
 * pernah keluar dan dicatat (jawaban no. 5 / PRD §3.5).
 */
class GradeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('wms.grade_default') as $kodeKategori => $daftarGrade) {
            $kategori = KategoriBarang::where('kode', $kodeKategori)->first();

            if (! $kategori) {
                continue;
            }

            foreach ($daftarGrade as $grade) {
                Grade::updateOrCreate(
                    [
                        'kode' => $grade['kode'],
                        'kategori_id' => $kategori->id,
                    ],
                    [
                        'keterangan' => $grade['keterangan'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
