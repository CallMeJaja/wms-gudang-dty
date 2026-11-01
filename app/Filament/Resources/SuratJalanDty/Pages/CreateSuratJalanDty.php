<?php

namespace App\Filament\Resources\SuratJalanDty\Pages;

use App\Filament\Concerns\CreatesPengeluaranRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSuratJalanDty extends CreateRecord
{
    use CreatesPengeluaranRecord;
    use LabelsCreateFormActions;

    protected static string $resource = SuratJalanDtyResource::class;
}
