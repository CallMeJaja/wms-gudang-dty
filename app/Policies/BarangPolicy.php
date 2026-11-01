<?php

namespace App\Policies;

use App\Models\Barang;
use App\Models\User;

/**
 * Otorisasi master data Barang.
 *
 * Admin   : full akses (create/update/delete).
 * Operator: hanya viewAny/view — melihat stok & master, tidak boleh mengubah.
 * Manajer : read-only — hanya viewAny/view.
 */
class BarangPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Barang $barang): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function update(User $user, Barang $barang): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function delete(User $user, Barang $barang): bool
    {
        return $user->is_active && $user->canManage();
    }
}
