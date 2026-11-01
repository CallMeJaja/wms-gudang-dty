<?php

namespace App\Filament\Resources\KonversiSatuans\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\KonversiSatuans\KonversiSatuanResource;
use App\Models\ActivityLog;
use App\Models\KonversiSatuan;
use Filament\Resources\Pages\CreateRecord;

class CreateKonversiSatuan extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = KonversiSatuanResource::class;

    protected function afterCreate(): void
    {
        /** @var KonversiSatuan $konversi */
        $konversi = $this->getRecord();
        $konversi->load(['barang', 'dariSatuan', 'keSatuan']);

        ActivityLog::catat(
            'CREATE',
            'MASTER_KONVERSI',
            (int) $konversi->id,
            'Tambah konversi: '.KonversiSatuanResource::ringkas($konversi),
        );
    }
}
