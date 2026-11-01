<?php

namespace App\Filament\Resources\PenerimaanPendukung\Pages;

use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPenerimaanPendukung extends ListRecords
{
    protected static string $resource = PenerimaanPendukungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => PenerimaanPendukungResource::canCreate()),
        ];
    }
}
