<?php

namespace App\Filament\Resources\PengeluaranScrap\Pages;

use App\Filament\Concerns\EditsPengeluaranRecord;
use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use Filament\Resources\Pages\EditRecord;

class EditPengeluaranScrap extends EditRecord
{
    use EditsPengeluaranRecord;

    protected static string $resource = PengeluaranScrapResource::class;
}
