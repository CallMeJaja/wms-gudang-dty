<?php

namespace App\Console\Commands;

use App\Services\StokMenipisService;
use Illuminate\Console\Command;

class CekStokMenipis extends Command
{
    protected $signature = 'wms:notifikasi-stok-menipis';

    protected $description = 'Periksa stok menipis dan kirim notifikasi database.';

    public function handle(StokMenipisService $stokMenipis): int
    {
        $stokMenipis->periksa();

        $this->info('Pemeriksaan notifikasi stok menipis selesai.');

        return self::SUCCESS;
    }
}
