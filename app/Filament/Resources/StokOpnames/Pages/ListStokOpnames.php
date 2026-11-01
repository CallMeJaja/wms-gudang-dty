<?php

namespace App\Filament\Resources\StokOpnames\Pages;

use App\Filament\Resources\StokOpnames\StokOpnameResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStokOpnames extends ListRecords
{
    protected static string $resource = StokOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => StokOpnameResource::canCreate()),
        ];
    }
}
