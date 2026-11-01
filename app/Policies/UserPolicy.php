<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function view(User $user, User $model): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function update(User $user, User $model): bool
    {
        return $user->is_active && $user->canManage();
    }

    /**
     * Pengguna tidak pernah dihapus — nonaktifkan lewat kolom is_active.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }
}
