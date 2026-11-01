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
        Notification::make()
            ->success()
            ->title('Pengguna berhasil ditambahkan')
            ->body('Akun baru sudah dapat digunakan untuk masuk ke panel.')
            ->send();
    }
}
