<?php

namespace App\Filament\Resources\PenerimaanPendukung\Pages;

use App\Filament\Concerns\CreatesPenerimaanRecord;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use App\Models\TransaksiMasuk;
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

        $nomor = trim((string) ($data['nomor_gate_pass'] ?? ''));
        $preview = app(NomorDokumenService::class)->peek('GATE_PASS');

        if ($nomor === '' || $nomor === $preview) {
            do {
                $nomor = app(NomorDokumenService::class)->generate('GATE_PASS');
            } while (TransaksiMasuk::query()->where('nomor_gate_pass', $nomor)->exists());
        }

        $data['nomor_gate_pass'] = $nomor;

        return $data;
    }
}
