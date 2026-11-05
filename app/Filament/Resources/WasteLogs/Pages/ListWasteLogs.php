<?php

namespace App\Filament\Resources\WasteLogs\Pages;

use App\Filament\Resources\WasteLogs\WasteLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWasteLogs extends ListRecords
{
    protected static string $resource = WasteLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => WasteLogResource::canCreate()),
        ];
    }
}
