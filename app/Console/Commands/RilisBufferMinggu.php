<?php

namespace App\Console\Commands;

use App\Services\RilisBufferService;
use Illuminate\Console\Command;

/**
 * Rilis buffer Minggu terjadwal (cron 05:45 Minggu WIB, routes/console.php).
 */
class RilisBufferMinggu extends Command
{
    protected $signature = 'wms:rilis-buffer-minggu';

    protected $description = 'Rilis seluruh buffer Minggu ke stok aktif.';

    public function handle(RilisBufferService $rilis): int
    {
        $saat = now();

        if (! $rilis->bolehRilisOtomatis($saat)) {
            $this->info('Bukan hari Minggu, rilis buffer dilewati.');

            return self::SUCCESS;
        }

        $hasil = $rilis->rilis($saat);

        $this->info(sprintf(
            'Buffer Minggu dirilis: %s kg (%d baris).',
            number_format($hasil['dirilis_kg'], 2, ',', '.'),
            $hasil['baris']
        ));

        return self::SUCCESS;
    }
}
