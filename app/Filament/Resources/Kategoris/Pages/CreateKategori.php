<?php

namespace App\Filament\Resources\Kategoris\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\Kategoris\KategoriResource;
use App\Models\ActivityLog;
use App\Models\KategoriBarang;
use Filament\Resources\Pages\CreateRecord;

class CreateKategori extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = KategoriResource::class;

    protected function afterCreate(): void
    {
        /** @var KategoriBarang $kategori */
        $kategori = $this->getRecord();

        ActivityLog::catat(
            'CREATE',
            'MASTER_KATEGORI',
            (int) $kategori->id,
            "Tambah kategori: {$kategori->kode} — {$kategori->nama}",
        );
    }
}
