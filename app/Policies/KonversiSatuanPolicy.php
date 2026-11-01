<?php

namespace App\Policies;

use App\Models\KonversiSatuan;
use App\Models\User;

/**
 * Otorisasi master data Konversi Satuan (PRD §2).
 */
class KonversiSatuanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, KonversiSatuan $konversi): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function update(User $user, KonversiSatuan $konversi): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function delete(User $user, KonversiSatuan $konversi): bool
    {
        return $user->is_active && $user->canManage();
    }
}
