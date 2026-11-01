<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case Manajer = 'manajer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin Gudang',
            self::Operator => 'Operator Gudang',
            self::Manajer => 'Manajer / Supervisor',
        };
    }

    /**
     * Role yang boleh melakukan operasi tulis (PRD §2).
     */
    public function canWrite(): bool
    {
        return match ($this) {
            self::Admin, self::Operator => true,
            self::Manajer => false,
        };
    }

    /**
     * Hanya Admin yang boleh void transaksi / ubah master data (PRD §2).
     */
    public function canManage(): bool
    {
        return $this === self::Admin;
    }
}
