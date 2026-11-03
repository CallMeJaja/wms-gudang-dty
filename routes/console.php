<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('wms:rilis-buffer-minggu')
    ->sundays()->at('05:45')
    ->timezone((string) config('app.timezone', 'Asia/Jakarta'));

Schedule::command('wms:notifikasi-stok-menipis')
    ->hourly()
    ->withoutOverlapping(30);
