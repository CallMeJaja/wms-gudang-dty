<?php

namespace App\Filament\Resources\PengeluaranPoy\Pages;

use App\Filament\Resources\PengeluaranPoy\PengeluaranPoyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengeluaranPoy extends ListRecords
{
    protected static string $resource = PengeluaranPoyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => PengeluaranPoyResource::canCreate()),
        ];
    }
}
