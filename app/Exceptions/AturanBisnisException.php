<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis yang terdefinisi di PRD.
 *
 * Dipakai service layer untuk menolak operasi yang secara teknis valid
 * tetapi melanggar aturan gudang (mis. konversi berat POY, grade lintas
 * kategori, alokasi buffer di luar hari kerja). Pesan exception
 * dirancang siap ditampilkan ke pengguna apa adanya.
 */
class AturanBisnisException extends RuntimeException
{
    /**
     * Pesan persis sesuai PRD A1b: POY tidak boleh dikonversi dari kemasan.
     */
    public const PESAN_POY_WAJIB_KG = 'Berat Netto (Kg) wajib diisi untuk POY — konversi otomatis tidak tersedia.';

    /**
     * Pesan persis sesuai PRD §5.2.2: buffer tidak boleh dipakai hari kerja.
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
     * Helper: grade tidak se-kategori dengan barang (PRD §7.5).
     */
    public static function gradeTidakSesuaiKategori(string $kodeBarang): self
    {
        return new self("Grade yang dipilih bukan milik kategori barang {$kodeBarang}.");
    }
}
