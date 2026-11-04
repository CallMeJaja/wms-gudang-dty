<?php

namespace App\Services;

class ShiftService
{
    public function current(): int
    {
        $jam = now()->format('H:i');

        foreach ((array) config('wms.shift', []) as $nomor => $shift) {
            $mulai = (string) ($shift['mulai'] ?? '');
            $selesai = (string) ($shift['selesai'] ?? '');

            if ($mulai === '' || $selesai === '') {
                continue;
            }

            $cocok = $mulai <= $selesai
                ? ($jam >= $mulai && $jam < $selesai)
                : ($jam >= $mulai || $jam < $selesai);

            if ($cocok) {
                return (int) $nomor;
            }
        }

        return 3;
    }

    public function label(int $nomor): string
    {
        $shift = config("wms.shift.{$nomor}");

        if (! is_array($shift)) {
            return "Shift {$nomor}";
        }

        return sprintf(
            'Shift %d (%s–%s)',
            $nomor,
            (string) ($shift['mulai'] ?? ''),
            (string) ($shift['selesai'] ?? ''),
        );
    }

    /**
     * @return array<int, string>
     */
    public function options(): array
    {
        $opsi = [];

        foreach ((array) config('wms.shift', []) as $nomor => $shift) {
            $opsi[(int) $nomor] = sprintf(
                '%d (%s–%s)',
                (int) $nomor,
                (string) ($shift['mulai'] ?? ''),
                (string) ($shift['selesai'] ?? ''),
            );
        }

        return $opsi;
    }
}
