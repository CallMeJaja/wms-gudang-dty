<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasteLog;

class WasteLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, WasteLog $wasteLog): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canWrite();
    }

    public function update(User $user, WasteLog $wasteLog): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function delete(User $user, WasteLog $wasteLog): bool
    {
        return $user->is_active && $user->canManage();
    }
}
