<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function view(User $user, ActivityLog $log): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ActivityLog $log): bool
    {
        return false;
    }

    public function delete(User $user, ActivityLog $log): bool
    {
        return false;
    }
}
