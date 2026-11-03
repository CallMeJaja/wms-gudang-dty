<?php

namespace Database\Seeders;

use App\Services\StokMenipisService;
use Illuminate\Database\Seeder;

/**
 * Menjalankan pemeriksaan stok menipis yang sebenarnya untuk data demo.
 */
class NotifikasiDemoSeeder extends Seeder
{
    public function run(StokMenipisService $stokMenipis): void
    {
        $stokMenipis->periksa();
    }
}
