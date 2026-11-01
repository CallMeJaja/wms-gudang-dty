<?php

namespace App\Policies;

use App\Models\StokBarang;
use App\Models\User;

/**
 * Stok barang bersifat baca-saja di panel (PRD §2).
 *
 * Semua role aktif boleh melihat posisi stok, tetapi tidak ada yang boleh
 * membuat/mengubah/menghapus baris saldo secara langsung — perubahan hanya
 * lewat service transaksi (penerimaan, pengeluaran, opname).
 */
class StokBarangPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, StokBarang $stokBarang): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, StokBarang $stokBarang): bool
    {
        return false;
    }

    public function delete(User $user, StokBarang $stokBarang): bool
    {
        return false;
    }
}
