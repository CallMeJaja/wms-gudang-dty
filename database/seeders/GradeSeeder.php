<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\KategoriBarang;
use Illuminate\Database\Seeder;

/**
 * Data awal grade yang bersumber dari config/wms.php.
 *
 * Grade DTY 'C' sengaja tetap di-seed dan aktif. Meskipun tidak muncul
 * di data logbook, scrap grade C tetap keluar dan dicatat, sehingga
 * grade tersebut harus tersedia.
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
