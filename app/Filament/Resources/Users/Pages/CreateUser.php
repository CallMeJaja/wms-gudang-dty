<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $aktif = (bool) $this->getRecord()->is_active;

        Notification::make()
            ->success()
            ->title('Pengguna berhasil ditambahkan')
            ->body($aktif
                ? 'Akun baru sudah dapat digunakan untuk masuk ke panel.'
                : 'Akun dibuat tidak aktif dan belum dapat masuk sampai diaktifkan.')
            ->send();
    }
}
