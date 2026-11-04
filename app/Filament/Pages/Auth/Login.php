<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function isUserAllowedToAccessPanel(Authenticatable $user): bool
    {
        if ($user instanceof User && ! $user->is_active) {
            throw ValidationException::withMessages([
                'data.email' => 'Akun tidak aktif. Hubungi Admin untuk mengaktifkan akun Anda.',
            ]);
        }

        return parent::isUserAllowedToAccessPanel($user);
    }
}
