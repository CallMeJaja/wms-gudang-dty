<?php

namespace App\Filament\Resources\PenerimaanPoy\Pages;

use App\Filament\Concerns\EditsPenerimaanRecord;
use App\Filament\Resources\PenerimaanPoy\PenerimaanPoyResource;
use Filament\Resources\Pages\EditRecord;

class EditPenerimaanPoy extends EditRecord
{
    use EditsPenerimaanRecord;

    protected static string $resource = PenerimaanPoyResource::class;
}
