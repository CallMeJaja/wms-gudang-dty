<?php

namespace App\Policies;

use App\Models\SupplierCustomer;
use App\Models\User;

/**
 * Otorisasi master data Supplier/Customer (PRD §2).
 * Hanya Admin yang boleh mengubah master partner.
 */
class SupplierCustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, SupplierCustomer $partner): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function update(User $user, SupplierCustomer $partner): bool
    {
        return $user->is_active && $user->canManage();
    }

    public function delete(User $user, SupplierCustomer $partner): bool
    {
        return $user->is_active && $user->canManage();
    }
}
