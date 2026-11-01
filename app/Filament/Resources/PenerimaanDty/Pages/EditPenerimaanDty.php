<?php

namespace App\Filament\Resources\PenerimaanDty\Pages;

use App\Filament\Concerns\EditsPenerimaanRecord;
use App\Filament\Resources\PenerimaanDty\PenerimaanDtyResource;
use Filament\Resources\Pages\EditRecord;

class EditPenerimaanDty extends EditRecord
{
    use EditsPenerimaanRecord;

    protected static string $resource = PenerimaanDtyResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['sumber_internal'] = $data['sumber_internal']
            ?: (string) config('wms.inbound.sumber_internal_dty', 'AREA PRODUKSI');

        return $data;
    }
}
