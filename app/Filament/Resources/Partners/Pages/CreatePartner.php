<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\Partners\PartnerResource;
use App\Models\ActivityLog;
use App\Models\SupplierCustomer;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = PartnerResource::class;

    protected function afterCreate(): void
    {
        /** @var SupplierCustomer $partner */
        $partner = $this->getRecord();

        ActivityLog::catat(
            'CREATE',
            'MASTER_PARTNER',
            (int) $partner->id,
            "Tambah partner: {$partner->kode} — {$partner->nama} ({$partner->jenis})",
        );
    }
}
