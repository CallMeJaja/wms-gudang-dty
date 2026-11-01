<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanKategoriWidget extends StatsOverviewWidget
{
    protected static ?bool $isLazyLoaded = false;

    protected ?string $heading = 'Ringkasan per Kategori';

    protected ?string $description = 'Total stok aktif + buffer per kategori (kg)';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return array_map(function (array $baris): Stat {
            return Stat::make(
                $baris['kode'],
                number_format($baris['totalKg'], 2, ',', '.').' kg'
            )
                ->description(sprintf(
                    'Aktif %s kg · Buffer %s kg',
                    number_format($baris['aktifKg'], 2, ',', '.'),
                    number_format($baris['bufferKg'], 2, ',', '.')
                ))
                ->color(match ($baris['kode']) {
                    'POY' => 'warning',
                    'DTY' => 'success',
                    default => 'gray',
                });
        }, DashboardData::ringkasanKategori());
    }
}
