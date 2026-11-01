<?php

namespace App\Services;

use App\Models\NomorDokumen;
use Illuminate\Support\Facades\DB;

/**
 * Penomoran dokumen atomik.
 *
 * Aturan yang dijaga:
 *  - Nomor digenerate di dalam DB::transaction() dengan lockForUpdate()
 *    pada baris `nomor_dokumen` (jenis + periode) sehingga aman untuk
 *    request paralel.
 *  - Baris periode berjalan dibuat firstOrCreate() bila belum ada
 *    (mis. periode bulan baru) sebelum dikunci.
 *  - Nilai `last_number` dinaikkan 1 lalu diformat memakai
 *    config('wms.format_nomor') + config('wms.bulan_romawi').
 *    Contoh: SURAT_JALAN_DTY Oktober 2026 nomor 244 -> 244/DTY/PEI/X/2026.
 */
class NomorDokumenService
{
    /**
     * Generate nomor berikutnya (menaikkan counter) dan kembalikan string terformat.
     */
    public function generate(string $jenis): string
    {
        $this->pastikanJenisValid($jenis);

        return DB::transaction(function () use ($jenis): string {
            $periode = NomorDokumen::periodeUntuk($jenis);

            // Baris untuk periode berjalan harus ada agar bisa dikunci.
            NomorDokumen::firstOrCreate(
                ['jenis' => $jenis, 'periode' => $periode],
                ['last_number' => 0]
            );

            // Row-level lock: hanya satu request yang boleh menaikkan counter.
            $baris = NomorDokumen::query()
                ->where('jenis', $jenis)
                ->where('periode', $periode)
                ->lockForUpdate()
                ->firstOrFail();

            $baris->last_number = (int) $baris->last_number + 1;
            $baris->save();

            return $this->format($jenis, (int) $baris->last_number);
        });
    }

    /**
     * Pratinjau nomor berikutnya TANPA menaikkan counter.
     *
     * Dipakai form untuk menampilkan nomor yang akan dipakai.
     * Tidak mengunci baris karena tidak ada penulisan.
     */
    public function peek(string $jenis): string
    {
        $this->pastikanJenisValid($jenis);

        $periode = NomorDokumen::periodeUntuk($jenis);

        $terakhir = (int) (NomorDokumen::query()
            ->where('jenis', $jenis)
            ->where('periode', $periode)
            ->value('last_number') ?? 0);

        return $this->format($jenis, $terakhir + 1);
    }

    /**
     * Format nomor sesuai config/wms.php (SSOT, tanpa angka hardcode).
     */
    public function format(string $jenis, int $nomor): string
    {
        $template = (string) config("wms.format_nomor.{$jenis}");

        $saat = now();

        return strtr($template, [
            '{nomor}' => (string) $nomor,
            '{periode}' => NomorDokumen::periodeUntuk($jenis, $saat),
            '{bulan_romawi}' => (string) config('wms.bulan_romawi.'.(int) $saat->format('n')),
            '{bulan}' => $saat->format('m'),
            '{tahun}' => $saat->format('Y'),
        ]);
    }

    /**
     * Tolak jenis dokumen yang tidak dikonfigurasi.
     */
    private function pastikanJenisValid(string $jenis): void
    {
        abort_unless(
            config("wms.format_nomor.{$jenis}") !== null,
            500,
            "Jenis dokumen '{$jenis}' tidak memiliki format penomoran di config/wms.php."
        );
    }
}
