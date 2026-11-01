<?php

namespace App\Enums;

enum JenisMutasi: string
{
    case Masuk = 'MASUK';
    case Keluar = 'KELUAR';
    case AdjustmentIn = 'ADJUSTMENT_IN';
    case AdjustmentOut = 'ADJUSTMENT_OUT';
    case AlokasiBuffer = 'ALOKASI_BUFFER';
    case RilisBuffer = 'RILIS_BUFFER';
    case Void = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::Masuk => 'Masuk',
            self::Keluar => 'Keluar',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::AlokasiBuffer => 'Alokasi ke Buffer',
            self::RilisBuffer => 'Rilis Buffer',
            self::Void => 'Pembatalan (Void)',
        };
    }

    /**
     * Alokasi & rilis buffer selalu menghasilkan DUA baris mutasi
     * (satu keluar dari pos asal, satu masuk ke pos tujuan) — PRD §7.4.
     */
    public function isPerpindahanPos(): bool
    {
        return in_array($this, [self::AlokasiBuffer, self::RilisBuffer], true);
    }
}
