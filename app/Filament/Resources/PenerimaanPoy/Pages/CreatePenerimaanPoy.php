<?php

namespace App\Filament\Resources\PenerimaanPoy\Pages;

use App\Filament\Concerns\CreatesPenerimaanRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\PenerimaanPoy\PenerimaanPoyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenerimaanPoy extends CreateRecord
{
    use CreatesPenerimaanRecord;
    use LabelsCreateFormActions;

    protected static string $resource = PenerimaanPoyResource::class;
}
