<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis gudang.
 *
 * Dipakai service layer untuk menolak operasi yang secara teknis valid
 * tetapi melanggar aturan gudang (mis. konversi berat POY, grade lintas
 * kategori, alokasi buffer di luar hari kerja). Pesan exception
 * dirancang siap ditampilkan ke pengguna apa adanya.
 */
class AturanBisnisException extends RuntimeException
{
    /**
     * Pesan baku: POY tidak boleh dikonversi dari kemasan.
     */
    public const PESAN_POY_WAJIB_KG = 'Berat Netto (Kg) wajib diisi untuk POY — konversi otomatis tidak tersedia.';

    /**
     * Pesan baku: buffer tidak boleh dipakai hari kerja.
     */
    public const PESAN_BUFFER_TIDAK_BOLEH_DIPAKAI = 'Stok aktif tidak cukup. Stok cadangan (untuk hari Minggu) tidak dapat digunakan hari ini.';

    /**
     * Helper: berat kg POY kosong.
     */
    public static function poyWajibKg(): self
    {
        return new self(self::PESAN_POY_WAJIB_KG);
    }

    /**
     * Helper: grade tidak se-kategori dengan barang.
     */
    public static function gradeTidakSesuaiKategori(string $kodeBarang): self
    {
        return new self("Grade yang dipilih bukan milik kategori barang {$kodeBarang}.");
    }
}
