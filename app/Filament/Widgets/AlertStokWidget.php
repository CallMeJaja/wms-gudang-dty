<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardData;
use Filament\Widgets\Widget;

class AlertStokWidget extends Widget
{
    protected static ?bool $isLazyLoaded = false;

    protected string $view = 'filament.widgets.alert-stok';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'alerts' => DashboardData::alerts(),
        ];
    }
}
