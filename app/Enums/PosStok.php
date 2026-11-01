<?php

namespace App\Enums;

/**
 * Pos stok: AKTIF (bebas dipakai) vs BUFFER (dikunci untuk hari Minggu).
 * Hanya pengeluaran POY yang memotong stok AKTIF.
 */
enum PosStok: string
{
    case Aktif = 'AKTIF';
    case Buffer = 'BUFFER';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Stok Aktif',
            self::Buffer => 'Stok Cadangan (Buffer)',
        };
    }
}
