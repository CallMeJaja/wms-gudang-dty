<?php

namespace App\Filament\Support;

use Illuminate\Validation\ValidationException;

/**
 * Menjembatani state form Filament ke kolom `transaksi_keluar_detail`.
 *
 * Berbeda dengan transaksi masuk, pengeluaran sudah memiliki kolom
 * `berat_gross_kg` sungguhan sehingga tidak ada penanda `[GROSS=..]` pada
 * keterangan. Semua normalisasi dikumpulkan di sini agar dapat diuji tanpa
 * Livewire dan menjadi pertahanan terakhir setelah validasi form.
 */
final class PengeluaranDetailMapper
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalisasi(array $data): array
    {
        $netto = self::numericOrNull($data['berat_netto_kg'] ?? null);
        $gross = self::numericOrNull($data['berat_gross_kg'] ?? null);

        if ($netto === null || $netto <= 0) {
            throw ValidationException::withMessages([
                'data.detail' => 'Berat Netto (Kg) wajib diisi dan lebih besar dari 0.',
            ]);
        }

        if ($gross !== null && $gross < $netto) {
            throw ValidationException::withMessages([
                'data.detail' => 'Berat Gross (Kg) tidak boleh lebih kecil dari Berat Netto (Kg).',
            ]);
        }

        $data['merge'] = trim((string) ($data['merge'] ?? ''));
        $data['jumlah_kemasan'] = self::numericOrNull($data['jumlah_kemasan'] ?? null);
        $data['jumlah_bobbin'] = self::integerOrNull($data['jumlah_bobbin'] ?? null);
        $data['berat_netto_kg'] = round($netto, 2);
        $data['berat_gross_kg'] = $gross === null ? null : round($gross, 2);
        $data['keterangan'] = self::teksAtauNull($data['keterangan'] ?? null);

        // Legacy form pengeluaran tidak memilih satuan kemasan.
        $data['satuan_kemasan_id'] = null;

        return $data;
    }

    public static function numericOrNull(mixed $nilai): ?float
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        return is_numeric($nilai) ? (float) $nilai : null;
    }

    public static function integerOrNull(mixed $nilai): ?int
    {
        $angka = self::numericOrNull($nilai);

        return $angka === null ? null : (int) round($angka);
    }

    private static function teksAtauNull(mixed $nilai): ?string
    {
        $teks = trim((string) $nilai);

        return $teks === '' ? null : mb_substr($teks, 0, 255);
    }
}
