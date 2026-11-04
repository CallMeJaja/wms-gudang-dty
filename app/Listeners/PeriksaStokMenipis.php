<?php

namespace App\Listeners;

use App\Events\StokBerubah;
use App\Services\StokMenipisService;

class PeriksaStokMenipis
{
    public function __construct(private readonly StokMenipisService $stokMenipis) {}

    public function handle(StokBerubah $event): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $this->stokMenipis->periksa($event->barangId);
    }
}
