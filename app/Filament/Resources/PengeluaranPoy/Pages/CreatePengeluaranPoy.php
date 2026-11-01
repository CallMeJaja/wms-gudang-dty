<?php

namespace App\Filament\Resources\PengeluaranPoy\Pages;

use App\Filament\Concerns\CreatesPengeluaranRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\PengeluaranPoy\PengeluaranPoyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengeluaranPoy extends CreateRecord
{
    use CreatesPengeluaranRecord;
    use LabelsCreateFormActions;

    protected static string $resource = PengeluaranPoyResource::class;
}
