<?php

namespace App\Filament\Resources\Barangs\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\Barangs\BarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBarang extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = BarangResource::class;
}
