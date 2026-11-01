<?php

namespace App\Filament\Resources\PenerimaanPendukung\Pages;

use App\Filament\Concerns\EditsPenerimaanRecord;
use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use Filament\Resources\Pages\EditRecord;

class EditPenerimaanPendukung extends EditRecord
{
    use EditsPenerimaanRecord;

    protected static string $resource = PenerimaanPendukungResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['nomor_gate_pass'] ?? null)) {
            $data['nomor_gate_pass'] = $this->getRecord()->nomor_gate_pass;
        }

        return $data;
    }
}
