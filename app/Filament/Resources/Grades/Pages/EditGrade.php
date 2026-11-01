<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use App\Models\ActivityLog;
use App\Models\Grade;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGrade extends EditRecord
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (Grade $record): bool => GradeResource::dilindungi($record))
                ->before(function (DeleteAction $action, Grade $record): void {
                    if ($alasan = GradeResource::alasanTidakBisaDihapus($record)) {
                        Notification::make()
                            ->danger()
                            ->title('Grade tidak dapat dihapus')
                            ->body($alasan)
                            ->send();

                        $action->halt();
                    }

                    ActivityLog::catat(
                        'DELETE',
                        'MASTER_GRADE',
                        (int) $record->id,
                        "Hapus grade: {$record->kode}",
                    );
                }),
        ];
    }

    /**
     * Defense-in-depth: identitas grade C DTY tidak boleh berubah dan
     * grade tersebut wajib tetap aktif meskipun field dinonaktifkan di UI.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $grade = $this->getRecord();

        if (GradeResource::dilindungi($grade)) {
            $data['kode'] = $grade->kode;
            $data['kategori_id'] = $grade->kategori_id;
            $data['is_active'] = true;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Grade $grade */
        $grade = $this->getRecord();

        ActivityLog::catat(
            'UPDATE',
            'MASTER_GRADE',
            (int) $grade->id,
            "Ubah grade: {$grade->kode} ({$grade->kategori?->kode}), aktif: "
            .($grade->is_active ? 'ya' : 'tidak'),
        );
    }
}
