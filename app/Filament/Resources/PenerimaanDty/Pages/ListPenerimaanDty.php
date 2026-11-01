<?php

namespace App\Filament\Resources\PenerimaanDty\Pages;

use App\Filament\Resources\PenerimaanDty\PenerimaanDtyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPenerimaanDty extends ListRecords
{
    protected static string $resource = PenerimaanDtyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => PenerimaanDtyResource::canCreate()),
        ];
    }
}
