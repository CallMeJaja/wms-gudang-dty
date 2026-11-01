<?php

namespace App\Filament\Resources\PenerimaanDty\Pages;

use App\Filament\Concerns\CreatesPenerimaanRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\PenerimaanDty\PenerimaanDtyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenerimaanDty extends CreateRecord
{
    use CreatesPenerimaanRecord {
        mutateFormDataBeforeCreate as protected siapkanDataDasar;
    }
    use LabelsCreateFormActions;

    protected static string $resource = PenerimaanDtyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->siapkanDataDasar($data);
        $data['sumber_internal'] = $data['sumber_internal']
            ?: (string) config('wms.inbound.sumber_internal_dty', 'AREA PRODUKSI');

        return $data;
    }
}
