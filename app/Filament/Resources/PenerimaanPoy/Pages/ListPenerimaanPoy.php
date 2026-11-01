<?php

namespace App\Filament\Resources\PenerimaanPoy\Pages;

use App\Filament\Resources\PenerimaanPoy\PenerimaanPoyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPenerimaanPoy extends ListRecords
{
    protected static string $resource = PenerimaanPoyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => PenerimaanPoyResource::canCreate()),
        ];
    }
}
