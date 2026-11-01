<?php

namespace App\Filament\Resources\Kategoris\Pages;

use App\Filament\Resources\Kategoris\KategoriResource;
use App\Models\ActivityLog;
use App\Models\KategoriBarang;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKategori extends EditRecord
{
    protected static string $resource = KategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, KategoriBarang $record): void {
                    if ($alasan = KategoriResource::alasanTidakBisaDihapus($record)) {
                        Notification::make()
                            ->danger()
                            ->title('Kategori tidak dapat dihapus')
                            ->body($alasan)
                            ->send();

                        $action->halt();
                    }

                    ActivityLog::catat(
                        'DELETE',
                        'MASTER_KATEGORI',
                        (int) $record->id,
                        "Hapus kategori: {$record->kode}",
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        /** @var KategoriBarang $kategori */
        $kategori = $this->getRecord();

        ActivityLog::catat(
            'UPDATE',
            'MASTER_KATEGORI',
            (int) $kategori->id,
            "Ubah kategori: {$kategori->kode} — {$kategori->nama}",
        );
    }
}
