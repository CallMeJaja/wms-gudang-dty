<?php

namespace App\Filament\Resources\KonversiSatuans\Pages;

use App\Filament\Resources\KonversiSatuans\KonversiSatuanResource;
use App\Models\ActivityLog;
use App\Models\KonversiSatuan;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKonversiSatuan extends EditRecord
{
    protected static string $resource = KonversiSatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, KonversiSatuan $record): void {
                    $record->load(['barang', 'dariSatuan', 'keSatuan']);

                    ActivityLog::catat(
                        'DELETE',
                        'MASTER_KONVERSI',
                        (int) $record->id,
                        'Hapus konversi: '.KonversiSatuanResource::ringkas($record),
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var KonversiSatuan $konversi */
        $konversi = $this->getRecord();
        $konversi->load(['barang', 'dariSatuan', 'keSatuan']);

        ActivityLog::catat(
            'UPDATE',
            'MASTER_KONVERSI',
            (int) $konversi->id,
            'Ubah konversi: '.KonversiSatuanResource::ringkas($konversi),
        );
    }
}
