<?php

namespace App\Filament\Resources\SuratJalanDty\Pages;

use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuratJalanDty extends ListRecords
{
    protected static string $resource = SuratJalanDtyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => SuratJalanDtyResource::canCreate()),
        ];
    }
}
