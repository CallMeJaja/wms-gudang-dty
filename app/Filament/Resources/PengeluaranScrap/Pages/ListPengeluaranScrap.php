<?php

namespace App\Filament\Resources\PengeluaranScrap\Pages;

use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengeluaranScrap extends ListRecords
{
    protected static string $resource = PengeluaranScrapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => PengeluaranScrapResource::canCreate()),
        ];
    }
}
