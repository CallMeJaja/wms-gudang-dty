<?php

namespace App\Filament\Resources\Satuans\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\Satuans\SatuanResource;
use App\Models\ActivityLog;
use App\Models\Satuan;
use Filament\Resources\Pages\CreateRecord;

class CreateSatuan extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = SatuanResource::class;

    protected function afterCreate(): void
    {
        /** @var Satuan $satuan */
        $satuan = $this->getRecord();

        ActivityLog::catat(
            'CREATE',
            'MASTER_SATUAN',
            (int) $satuan->id,
            "Tambah satuan: {$satuan->kode} — {$satuan->nama}",
        );
    }
}
