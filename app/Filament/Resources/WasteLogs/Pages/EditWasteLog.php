<?php

namespace App\Filament\Resources\WasteLogs\Pages;

use App\Filament\Resources\WasteLogs\WasteLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWasteLog extends EditRecord
{
    protected static string $resource = WasteLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
