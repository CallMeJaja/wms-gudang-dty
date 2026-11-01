<?php

namespace App\Filament\Resources\PengeluaranPoy\Pages;

use App\Filament\Concerns\EditsPengeluaranRecord;
use App\Filament\Resources\PengeluaranPoy\PengeluaranPoyResource;
use Filament\Resources\Pages\EditRecord;

class EditPengeluaranPoy extends EditRecord
{
    use EditsPengeluaranRecord;

    protected static string $resource = PengeluaranPoyResource::class;
}
