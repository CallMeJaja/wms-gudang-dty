<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Role;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var User $record */
        $record = $this->getRecord();
        $actor = Auth::user();

        $newRole = $this->roleValue($data['role'] ?? $record->role);
        $newIsActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : (bool) $record->is_active;

        $wasActiveAdmin = $record->role === Role::Admin && (bool) $record->is_active;
        $willRemainActiveAdmin = $newRole === Role::Admin && $newIsActive;

        if ($wasActiveAdmin && ! $willRemainActiveAdmin) {
            $otherActiveAdmins = User::query()
                ->where('role', Role::Admin->value)
                ->where('is_active', true)
                ->whereKeyNot($record->getKey())
                ->count();

            if ($otherActiveAdmins === 0) {
                throw ValidationException::withMessages([
                    $newRole !== Role::Admin ? 'data.role' : 'data.is_active' => 'Minimal harus ada satu Admin aktif.',
                ]);
            }
        }

        if ($actor !== null && $record->is($actor)) {
            if ($newRole !== Role::Admin) {
                throw ValidationException::withMessages([
                    'data.role' => 'Peran akun sendiri tidak dapat diubah dari Admin.',
                ]);
            }

            if (! $newIsActive) {
                throw ValidationException::withMessages([
                    'data.is_active' => 'Akun sendiri tidak dapat dinonaktifkan.',
                ]);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->success()
            ->title('Pengguna berhasil diperbarui')
            ->send();
    }

    private function roleValue(mixed $state): ?Role
    {
        if ($state instanceof Role) {
            return $state;
        }

        return is_string($state) ? Role::tryFrom($state) : null;
    }
}
