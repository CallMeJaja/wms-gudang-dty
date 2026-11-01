<?php

namespace App\Services;

/**
 * Status kesiapan buffer Minggu.
 *
 * Objek nilai read-only yang dipakai widget dashboard. Nilai level
 * sengaja berupa string tetap ("SIAP" / "BELUM_SIAP" / "TERLAMBAT")
 * agar widget dapat memetakan warna tanpa logika tambahan.
 */
final readonly class StatusBufferMinggu
{
    public const SIAP = 'SIAP';

    public const BELUM_SIAP = 'BELUM_SIAP';

    public const TERLAMBAT = 'TERLAMBAT';

    public function __construct(
        public float $bufferKg,
        public float $bufferPallet,
        public int $targetPallet,
        public float $targetKg,
        public float $sisaKuotaKg,
        public int $sisaHariKerja,
        public string $level,
        public string $label,
        public string $pesan,
        public string $warna,
    ) {}
}
