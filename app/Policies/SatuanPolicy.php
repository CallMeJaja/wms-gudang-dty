<?php

namespace App\Policies;

use App\Models\Satuan;
use App\Models\User;

/**
 * Otorisasi master data Satuan (PRD §2).
 *
 * Admin   : full akses (create/update/delete).
 * Operator: hanya viewAny/view — melihat stok & master, tidak boleh mengubah.
 * Manajer : read-only — hanya viewAny/view.
 */
class SatuanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Satuan $satuan): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function update(User $user, Satuan $satuan): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function delete(User $user, Satuan $satuan): bool
    {
        return $user->is_active && $user->canManage();
    }
}
