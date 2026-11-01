<?php

namespace App\Filament\Concerns;

trait LabelsCreateFormActions
{
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Simpan'),
            $this->getCancelFormAction(),
        ];
    }
}
