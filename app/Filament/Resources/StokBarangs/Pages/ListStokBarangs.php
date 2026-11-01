<?php

namespace App\Filament\Resources\StokBarangs\Pages;

use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Resources\Pages\ListRecords;

class ListStokBarangs extends ListRecords
{
    protected static string $resource = StokBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
