<?php

namespace App\Filament\Support;

/**
 * Komponen/opsi form netral yang dipakai lintas modul transaksi.
 */
final class WmsForm
{
    /**
     * Opsi shift dari config/wms.php (SSOT).
     *
     * @return array<int, string>
     */
    public static function opsiShift(): array
    {
        $opsi = [];

        foreach ((array) config('wms.shift', []) as $nomor => $jam) {
            $opsi[(int) $nomor] = sprintf('%d (%s–%s)', $nomor, $jam['mulai'], $jam['selesai']);
        }

        return $opsi;
    }

    /**
     * Jumlahkan berat netto dari state repeater.
     */
    public static function totalNetto(mixed $detail): float
    {
        return self::totalKolom($detail, 'berat_netto_kg');
    }

    /**
     * Jumlahkan berat gross dari state repeater.
     */
    public static function totalGross(mixed $detail): float
    {
        return self::totalKolom($detail, 'berat_gross_kg');
    }

    private static function totalKolom(mixed $detail, string $kolom): float
    {
        if (! is_array($detail)) {
            return 0.0;
        }

        return round(array_sum(array_map(
            fn ($baris): float => is_array($baris) && is_numeric($baris[$kolom] ?? null)
                ? (float) $baris[$kolom]
                : 0.0,
            $detail,
        )), 2);
    }
}
