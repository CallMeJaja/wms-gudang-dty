<?php

namespace App\Enums;

/**
 * Pos stok: AKTIF (bebas dipakai) vs BUFFER (dikunci untuk hari Minggu).
 * Lihat PRD §5.2.1 & A3.
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
