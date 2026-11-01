<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

/**
 * Otorisasi master data Grade (PRD §2).
 *
 * Aturan bisnis tambahan (grade C DTY tidak boleh dihapus/nonaktif)
 * TIDAK ditegakkan di sini, melainkan di resource/form (GradeResource)
 * agar pesan validasi tampil per-field.
 */
class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Grade $grade): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->is_active && $user->canManage();
    }
}
