<?php

namespace App\Filament\Resources\WasteLogs\Pages;

use App\Filament\Resources\WasteLogs\WasteLogResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWasteLog extends CreateRecord
{
    protected static string $resource = WasteLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}
