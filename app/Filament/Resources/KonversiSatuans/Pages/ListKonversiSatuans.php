<?php

namespace App\Filament\Resources\KonversiSatuans\Pages;

use App\Filament\Resources\KonversiSatuans\KonversiSatuanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKonversiSatuans extends ListRecords
{
    protected static string $resource = KonversiSatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
