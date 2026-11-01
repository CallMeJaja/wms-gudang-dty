<?php

namespace App\Filament\Support;

/**
 * Komponen/opsi form yang dipakai bersama ketiga jenis penerimaan.
 */
final class PenerimaanForm
{
    /**
     * Opsi shift dari config/wms.php (bukan hardcode).
     *
     * @return array<int, string>
     */
    public static function opsiShift(): array
    {
        return WmsForm::opsiShift();
    }

    /**
     * Jumlahkan berat_kg dari state repeater (dipakai placeholder total).
     */
    public static function totalBerat(mixed $detail): float
    {
        if (! is_array($detail)) {
            return 0.0;
        }

        return round(array_sum(array_map(
            fn ($baris): float => is_array($baris) ? (float) ($baris['berat_kg'] ?? 0) : 0.0,
            $detail,
        )), 2);
    }
}
