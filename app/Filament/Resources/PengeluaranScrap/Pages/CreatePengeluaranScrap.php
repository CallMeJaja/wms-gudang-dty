<?php

namespace App\Filament\Resources\PengeluaranScrap\Pages;

use App\Filament\Concerns\CreatesPengeluaranRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengeluaranScrap extends CreateRecord
{
    use CreatesPengeluaranRecord;
    use LabelsCreateFormActions;

    protected static string $resource = PengeluaranScrapResource::class;
}
