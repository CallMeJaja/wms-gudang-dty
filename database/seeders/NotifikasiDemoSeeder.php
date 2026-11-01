<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Seeder;

/**
 * Notifikasi demo untuk admin agar badge dan drawer notifikasi sidebar terlihat.
 */
class NotifikasiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', Role::Admin->value)->first();

        if ($admin === null || $admin->notifications()->exists()) {
            return;
        }

        Notification::make()
            ->title('Stok DTY Coning Oil menipis')
            ->body('Sisa 195 kg dari minimum 200 kg.')
            ->icon('heroicon-o-exclamation-triangle')
            ->warning()
            ->sendToDatabase($admin);

        Notification::make()
            ->title('Buffer Minggu belum siap')
            ->body('Buffer baru 1 dari 6 pallet. Sisa 6 hari kerja.')
            ->icon('heroicon-o-exclamation-triangle')
            ->warning()
            ->sendToDatabase($admin);

        Notification::make()
            ->title('Stok aktif POY kritis')
            ->body('Stok aktif di bawah kebutuhan 1 hari kerja.')
            ->icon('heroicon-o-exclamation-triangle')
            ->danger()
            ->sendToDatabase($admin);
    }
}
