<?php

namespace App\Policies;

use App\Enums\StatusDokumen;
use App\Models\TransaksiMasuk;
use App\Models\User;

/**
 * Otorisasi resource Penerimaan Filament (transaksi masuk).
 *
 * Dipakai bersama oleh resource Penerimaan POY, Penerimaan Pendukung, dan
 * Penerimaan DTY lewat `Gate::authorize('create', [TransaksiMasuk::class, ...])`.
 *
 * - Admin & Operator : boleh input transaksi (create/update/posting/issue/delete
 *   selama masih DRAFT). Operator memang harus bisa input transaksi.
 * - Manajer          : read-only (viewAny/view saja).
 * - Void transaksi   : hanya Admin; guard disiapkan agar aksi void tidak perlu
 *   mengubah policy.
 *
 * Setiap aksi menerima argumen opsional `$jenisMasuk` (JenisMasuk|string|null)
 * sehingga resource dapat memastikan hanya jenis penerimaan yang dikelolanya
 * yang boleh disentuh — mencegah mis. aksi dty pada data poy.
 */
class PenerimaanPolicy
{
    public function viewAny(User $user, $jenisMasuk = null): bool
    {
        return $user->is_active;
    }

    public function view(User $user, TransaksiMasuk $transaksi, $jenisMasuk = null): bool
    {
        return $user->is_active && $this->jenisCocok($transaksi, $jenisMasuk);
    }

    public function create(User $user, $jenisMasuk = null): bool
    {
        return $user->is_active && $user->canWrite();
    }

    /**
     * Ubah header/detail — hanya selama transaksi masih DRAFT.
     */
    public function update(User $user, TransaksiMasuk $transaksi, $jenisMasuk = null): bool
    {
        return $user->is_active
            && $user->canWrite()
            && $this->jenisCocok($transaksi, $jenisMasuk)
            && $this->masihDraf($transaksi);
    }

    /**
     * DRAFT -> POSTED (transaksi internal) atau DRAFT -> ISSUED (dokumen resmi).
     */
    public function posting(User $user, TransaksiMasuk $transaksi, $jenisMasuk = null): bool
    {
        return $user->is_active
            && $user->canWrite()
            && $this->jenisCocok($transaksi, $jenisMasuk)
            && $this->masihDraf($transaksi);
    }

    /**
     * Alias semantik untuk dokumen resmi (Gate Pass) — DRAFT -> ISSUED.
     */
    public function issue(User $user, TransaksiMasuk $transaksi, $jenisMasuk = null): bool
    {
        return $this->posting($user, $transaksi, $jenisMasuk);
    }

    /**
     * Hapus transaksi — hanya DRAFT yang belum mempengaruhi stok.
     */
    public function delete(User $user, TransaksiMasuk $transaksi, $jenisMasuk = null): bool
    {
        return $user->is_active
            && $user->canWrite()
            && $this->jenisCocok($transaksi, $jenisMasuk)
            && $this->masihDraf($transaksi);
    }

    /**
     * Void (POSTED/ISSUED -> VOID) — hanya Admin.
     * Dipakai tombol "Batalkan (Void)" pada ketiga resource penerimaan.
     */
    public function void(User $user, TransaksiMasuk $transaksi, $jenisMasuk = null): bool
    {
        return $user->is_active
            && $user->canManage()
            && $this->jenisCocok($transaksi, $jenisMasuk)
            && in_array($transaksi->status, [StatusDokumen::Posted, StatusDokumen::Issued], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    private function masihDraf(TransaksiMasuk $transaksi): bool
    {
        return $transaksi->status === StatusDokumen::Draft;
    }

    /**
     * Pemanggilan `Gate::authorize($ability, [$transaksi, $jenis])` meneruskan
     * $jenis sebagai argumen ketiga; bila null dianggap "jenis apa pun" (halaman
     * index lintas jenis).
     */
    private function jenisCocok(TransaksiMasuk $transaksi, $jenisMasuk): bool
    {
        if ($jenisMasuk === null) {
            return true;
        }

        $nilai = $jenisMasuk instanceof \BackedEnum ? $jenisMasuk->value : (string) $jenisMasuk;

        return $transaksi->jenis_masuk?->value === $nilai;
    }
}
