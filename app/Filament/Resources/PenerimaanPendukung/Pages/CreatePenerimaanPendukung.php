<?php

namespace App\Filament\Resources\PenerimaanPendukung\Pages;

use App\Filament\Concerns\CreatesPenerimaanRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use App\Services\NomorDokumenService;
use Filament\Resources\Pages\CreateRecord;

class CreatePenerimaanPendukung extends CreateRecord
{
    use CreatesPenerimaanRecord {
        mutateFormDataBeforeCreate as protected siapkanDataDasar;
    }
    use LabelsCreateFormActions;

    protected static string $resource = PenerimaanPendukungResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->siapkanDataDasar($data);

        if (blank($data['nomor_gate_pass'] ?? null)) {
            $data['nomor_gate_pass'] = app(NomorDokumenService::class)->generate('GATE_PASS');
        }

        return $data;
    }
}
