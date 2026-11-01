<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\Grades\GradeResource;
use App\Models\ActivityLog;
use App\Models\Grade;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = GradeResource::class;

    protected function afterCreate(): void
    {
        /** @var Grade $grade */
        $grade = $this->getRecord();

        ActivityLog::catat(
            'CREATE',
            'MASTER_GRADE',
            (int) $grade->id,
            "Tambah grade: {$grade->kode} ({$grade->kategori?->kode})",
        );
    }
}
