<?php

namespace App\Filament\Resources\TransaksiMasuks\Pages;

use App\Filament\Resources\TransaksiMasuks\TransaksiMasukResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiMasuk extends EditRecord
{
    protected static string $resource = TransaksiMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
