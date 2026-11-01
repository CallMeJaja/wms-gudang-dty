<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Resources\Partners\PartnerResource;
use App\Models\ActivityLog;
use App\Models\SupplierCustomer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPartner extends EditRecord
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, SupplierCustomer $record): void {
                    ActivityLog::catat(
                        'DELETE',
                        'MASTER_PARTNER',
                        (int) $record->id,
                        "Hapus mitra (soft delete): {$record->kode}",
                    );
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var SupplierCustomer $partner */
        $partner = $this->getRecord();

        ActivityLog::catat(
            'UPDATE',
            'MASTER_PARTNER',
            (int) $partner->id,
            "Ubah mitra: {$partner->kode} — {$partner->nama} ({$partner->jenis})",
        );
    }
}
