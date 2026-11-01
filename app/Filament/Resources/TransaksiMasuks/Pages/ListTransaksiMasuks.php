<?php

namespace App\Filament\Resources\TransaksiMasuks\Pages;

use App\Filament\Resources\TransaksiMasuks\TransaksiMasukResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiMasuks extends ListRecords
{
    protected static string $resource = TransaksiMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
