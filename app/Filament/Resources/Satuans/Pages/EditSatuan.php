<?php

namespace App\Filament\Resources\Satuans\Pages;

use App\Filament\Resources\Satuans\SatuanResource;
use App\Models\ActivityLog;
use App\Models\Satuan;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSatuan extends EditRecord
{
    protected static string $resource = SatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, Satuan $record): void {
                    if ($alasan = SatuanResource::alasanTidakBisaDihapus($record)) {
                        Notification::make()
                            ->danger()
                            ->title('Satuan tidak dapat dihapus')
                            ->body($alasan)
                            ->send();

                        $action->halt();
                    }

                    ActivityLog::catat(
                        'DELETE',
                        'MASTER_SATUAN',
                        (int) $record->id,
                        "Hapus satuan: {$record->kode}",
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Satuan $satuan */
        $satuan = $this->getRecord();

        ActivityLog::catat(
            'UPDATE',
            'MASTER_SATUAN',
            (int) $satuan->id,
            "Ubah satuan: {$satuan->kode} — {$satuan->nama}",
        );
    }
}
