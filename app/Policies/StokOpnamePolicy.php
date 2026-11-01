<?php

namespace App\Policies;

use App\Models\StokOpname;
use App\Models\User;

class StokOpnamePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function view(User $user, StokOpname $opname): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }
}
