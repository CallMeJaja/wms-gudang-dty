<?php

namespace App\Policies;

use App\Enums\StatusDokumen;
use App\Models\TransaksiKeluar;
use App\Models\User;

/**
 * Otorisasi transaksi pengeluaran / transaksi keluar (F3, PRD §2).
 *
 * Cerminan PenerimaanPolicy:
 * - Admin & Operator: boleh input transaksi (create/update/posting/issue/delete
 *   selama masih DRAFT).
 * - Manajer: read-only (viewAny/view saja).
 * - Void: hanya Admin & status POSTED/ISSUED.
 *
 * Setiap aksi menerima argumen opsional `$jenisKeluar` sehingga controller
 * dapat memastikan hanya jenis yang dikelolanya yang boleh disentuh.
 */
class PengeluaranPolicy
{
    public function viewAny(User $user, $jenisKeluar = null): bool
    {
        return $user->is_active;
    }

    public function view(User $user, TransaksiKeluar $transaksi, $jenisKeluar = null): bool
    {
        return $user->is_active && $this->jenisCocok($transaksi, $jenisKeluar);
    }

    public function create(User $user, $jenisKeluar = null): bool
    {
        return $user->is_active && $user->canWrite();
    }

    public function update(User $user, TransaksiKeluar $transaksi, $jenisKeluar = null): bool
    {
        return $user->is_active
            && $user->canWrite()
            && $this->jenisCocok($transaksi, $jenisKeluar)
            && $this->masihDraf($transaksi);
    }

    public function posting(User $user, TransaksiKeluar $transaksi, $jenisKeluar = null): bool
    {
        return $user->is_active
            && $user->canWrite()
            && $this->jenisCocok($transaksi, $jenisKeluar)
            && $this->masihDraf($transaksi);
    }

    public function issue(User $user, TransaksiKeluar $transaksi, $jenisKeluar = null): bool
    {
        return $this->posting($user, $transaksi, $jenisKeluar);
    }

    public function delete(User $user, TransaksiKeluar $transaksi, $jenisKeluar = null): bool
    {
        return $user->is_active
            && $user->canWrite()
            && $this->jenisCocok($transaksi, $jenisKeluar)
            && $this->masihDraf($transaksi);
    }

    public function void(User $user, TransaksiKeluar $transaksi, $jenisKeluar = null): bool
    {
        return $user->is_active
            && $user->canManage()
            && $this->jenisCocok($transaksi, $jenisKeluar)
            && in_array($transaksi->status, [StatusDokumen::Posted, StatusDokumen::Issued], true);
    }

    private function masihDraf(TransaksiKeluar $transaksi): bool
    {
        return $transaksi->status === StatusDokumen::Draft;
    }

    private function jenisCocok(TransaksiKeluar $transaksi, $jenisKeluar): bool
    {
        if ($jenisKeluar === null) {
            return true;
        }

        $nilai = $jenisKeluar instanceof \BackedEnum ? $jenisKeluar->value : (string) $jenisKeluar;

        return $transaksi->jenis_keluar?->value === $nilai;
    }
}
