<?php

namespace App\Filament\Resources\SuratJalanDty\Pages;

use App\Filament\Concerns\EditsPengeluaranRecord;
use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use Filament\Resources\Pages\EditRecord;

class EditSuratJalanDty extends EditRecord
{
    use EditsPengeluaranRecord;

    protected static string $resource = SuratJalanDtyResource::class;
}
