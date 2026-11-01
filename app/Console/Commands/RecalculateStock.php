<?php

namespace App\Console\Commands;

use App\Models\MutasiStok;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateStock extends Command
{
    protected $signature = 'wms:recalculate-stock {--dry-run : Pratinjau tanpa menulis perubahan}';

    protected $description = 'Hitung ulang saldo_akhir_qty mutasi_stok per posisi dari awal.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $diperbaiki = 0;
        $diperiksa = 0;

        MutasiStok::query()
            ->select(['barang_id', 'merge', 'grade_id', 'pos'])
            ->distinct()
            ->orderBy('barang_id')
            ->orderBy('merge')
            ->orderBy('grade_id')
            ->orderBy('pos')
            ->chunk(200, function ($kelompok) use ($dryRun, &$diperbaiki, &$diperiksa): void {
                foreach ($kelompok as $kunci) {
                    [$jumlah, $periksa] = $this->prosesKelompok(
                        (int) $kunci->barang_id,
                        (string) $kunci->merge,
                        (int) $kunci->grade_id,
                        $kunci->pos instanceof \BackedEnum ? $kunci->pos->value : (string) $kunci->pos,
                        $dryRun,
                    );
                    $diperbaiki += $jumlah;
                    $diperiksa += $periksa;
                }
            });

        $this->info(sprintf(
            '%s %d baris mutasi diperiksa, %d diperbaiki.',
            $dryRun ? '[DRY-RUN] Selesai:' : 'Selesai:',
            $diperiksa,
            $diperbaiki,
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{int, int} [diperbaiki, diperiksa]
     */
    private function prosesKelompok(int $barangId, string $merge, int $gradeId, string $pos, bool $dryRun): array
    {
        $diperbaiki = 0;
        $diperiksa = 0;
        $berjalan = 0.0;

        MutasiStok::query()
            ->where('barang_id', $barangId)
            ->where('merge', $merge)
            ->where('grade_id', $gradeId)
            ->where('pos', $pos)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->chunk(500, function ($barisList) use ($dryRun, &$diperbaiki, &$diperiksa, &$berjalan): void {
                foreach ($barisList as $baris) {
                    $diperiksa++;
                    $berjalan = round($berjalan + (float) $baris->masuk_qty - (float) $baris->keluar_qty, 2);

                    if (abs($berjalan - (float) $baris->saldo_akhir_qty) < 0.005) {
                        continue;
                    }

                    $diperbaiki++;

                    if (! $dryRun) {
                        DB::table('mutasi_stok')
                            ->where('id', $baris->getKey())
                            ->update(['saldo_akhir_qty' => $berjalan]);
                    }
                }
            });

        return [$diperbaiki, $diperiksa];
    }
}
