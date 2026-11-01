<?php

namespace App\Exceptions;

use App\Enums\PosStok;

/**
 * Saldo stok pada pos tertentu tidak mencukupi untuk operasi keluar.
 *
 * Dilempar oleh App\Services\StokService::kurangiStok() dan
 * rilisBuffer() sehingga pemanggil (controller) dapat
 * mengembalikan respons 422 tanpa menebak pesan.
 */
class StokTidakCukupException extends AturanBisnisException
{
    public function __construct(
        public readonly string $kodeBarang,
        public readonly PosStok $pos,
        public readonly float $diminta,
        public readonly float $tersedia,
    ) {
        parent::__construct(sprintf(
            'Stok %s untuk %s tidak cukup: diminta %s, tersedia %s.',
            $pos->label(),
            $kodeBarang,
            self::angka($diminta),
            self::angka($tersedia),
        ));
    }

    /**
     * Format angka gaya Indonesia (koma desimal) untuk pesan pengguna.
     */
    private static function angka(float $nilai): string
    {
        return number_format($nilai, 2, ',', '.');
    }
}
