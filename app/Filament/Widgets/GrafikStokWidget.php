<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardData;
use Filament\Widgets\ChartWidget;

class GrafikStokWidget extends ChartWidget
{
    protected static ?bool $isLazyLoaded = false;

    protected ?string $heading = 'Stok Harian 30 Hari';

    protected ?string $description = 'POY & DTY masuk vs keluar (kg)';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $grafik = DashboardData::grafikHarian();

        return [
            'labels' => $grafik['labels'],
            'datasets' => [
                [
                    'label' => 'POY masuk',
                    'data' => $grafik['masukPoy'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'POY keluar',
                    'data' => $grafik['keluarPoy'],
                    'borderColor' => '#ea580c',
                    'backgroundColor' => 'rgba(234, 88, 12, 0.1)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'DTY masuk',
                    'data' => $grafik['masukDty'],
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'DTY keluar',
                    'data' => $grafik['keluarDty'],
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'tension' => 0.35,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
