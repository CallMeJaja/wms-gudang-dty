<?php

namespace App\Services;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\StatusDokumen;
use App\Models\MutasiStok;
use App\Models\TransaksiKeluarDetail;
use App\Models\TransaksiMasukDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Agregasi baca-saja untuk laporan gudang.
 *
 * Laporan keluar-masuk & kartu stok membaca `mutasi_stok` (otomatis hanya
 * berisi transaksi POSTED/ISSUED + baris VOID bertipe VOID). Rendemen
 * membandingkan POY masuk dari IPCI dengan DTY keluar via Surat Jalan.
 */
class LaporanService
{
    /**
     * @return array{dari: string, sampai: string}
     */
    public static function defaultPeriode(): array
    {
        return [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
        ];
    }

    /**
     * Periode 30 hari terakhir termasuk hari ini, dipakai khusus rendemen.
     *
     * @return array{dari: string, sampai: string}
     */
    public static function defaultPeriodeRendemen(): array
    {
        $sampai = now()->startOfDay();

        return [
            'dari' => $sampai->copy()->subDays(29)->toDateString(),
            'sampai' => $sampai->toDateString(),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function petaArus(): array
    {
        return [
            'MASUK' => [JenisMutasi::Masuk->value, JenisMutasi::AdjustmentIn->value, JenisMutasi::RilisBuffer->value],
            'KELUAR' => [JenisMutasi::Keluar->value, JenisMutasi::AdjustmentOut->value, JenisMutasi::AlokasiBuffer->value],
            'VOID' => [JenisMutasi::Void->value],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | A. Laporan Keluar-Masuk
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array{dari?: ?string, sampai?: ?string, kategori?: ?string, arus?: ?string, merge?: ?string}  $filter
     * @return Collection<int, MutasiStok>
     */
    public function barisKeluarMasuk(array $filter): Collection
    {
        return $this->queryKeluarMasuk($filter)->get();
    }

    /**
     * @param  array{dari?: ?string, sampai?: ?string, kategori?: ?string, arus?: ?string, merge?: ?string}  $filter
     */
    public function queryKeluarMasuk(array $filter): Builder
    {
        $peta = self::petaArus();

        return MutasiStok::query()
            ->with(['barang.kategori', 'grade'])
            ->when($filter['dari'] ?? null, fn (Builder $q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn (Builder $q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['kategori'] ?? null, fn (Builder $q, $kode) => $q->whereHas(
                'barang.kategori', fn (Builder $k) => $k->where('kode', $kode)
            ))
            ->when($filter['arus'] ?? null, fn (Builder $q, $arus) => $q->whereIn(
                'jenis_transaksi', $peta[$arus] ?? [$arus]
            ))
            ->when(trim((string) ($filter['merge'] ?? '')) !== '', fn (Builder $q) => $q->where(
                'merge', 'like', '%'.trim((string) $filter['merge']).'%'
            ))
            ->orderBy('tanggal')
            ->orderBy('id');
    }

    /**
     * @param  Collection<int, MutasiStok>  $baris
     * @return array{masuk: float, keluar: float, bersih: float}
     */
    public function totalKeluarMasuk(Collection $baris): array
    {
        $masuk = (float) $baris->sum('masuk_qty');
        $keluar = (float) $baris->sum('keluar_qty');

        return ['masuk' => $masuk, 'keluar' => $keluar, 'bersih' => $masuk - $keluar];
    }

    /*
    |--------------------------------------------------------------------------
    | B. Kartu Stok
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{saldoAwal: float, baris: Collection<int, MutasiStok>}
     */
    public function kartuStok(int $barangId, string $merge, ?int $gradeId, ?string $pos, string $dari, string $sampai): array
    {
        $dasar = fn (): Builder => MutasiStok::query()
            ->where('barang_id', $barangId)
            ->where('merge', $merge)
            ->when($gradeId !== null, fn (Builder $q) => $q->where('grade_id', $gradeId))
            ->when($pos !== null && $pos !== '', fn (Builder $q) => $q->where('pos', $pos));

        $saldoAwal = (float) ($dasar()
            ->whereDate('tanggal', '<', $dari)
            ->selectRaw('COALESCE(SUM(masuk_qty), 0) - COALESCE(SUM(keluar_qty), 0) AS net')
            ->value('net'));

        $baris = $dasar()
            ->with(['barang', 'grade'])
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $jalan = $saldoAwal;

        foreach ($baris as $mutasi) {
            $jalan += (float) $mutasi->masuk_qty - (float) $mutasi->keluar_qty;
            $mutasi->setAttribute('saldo_berjalan', round($jalan, 2));
        }

        return ['saldoAwal' => round($saldoAwal, 2), 'baris' => $baris];
    }

    /*
    |--------------------------------------------------------------------------
    | C. Rendemen POY Masuk vs DTY Keluar
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{totalPoy: float, totalDty: float, rendemen: ?float, waste: ?float, wasteKg: float, targetKg: float, pencapaianTarget: ?float, selisihTargetKg: float, surplus: bool, perMerge: Collection<int, array<string, mixed>>}
     */
    public function rendemen(string $dari, string $sampai): array
    {
        $totalPoy = round((float) TransaksiMasukDetail::query()
            ->whereHas('transaksi', fn (Builder $q) => $q
                ->where('jenis_masuk', JenisMasuk::PoyIpci->value)
                ->where('status', StatusDokumen::Posted->value)
                ->whereDate('tanggal', '>=', $dari)
                ->whereDate('tanggal', '<=', $sampai))
            ->sum('berat_kg'), 2);

        $totalDty = round((float) TransaksiKeluarDetail::query()
            ->whereHas('transaksi', fn (Builder $q) => $q
                ->where('jenis_keluar', JenisKeluar::DtySuratJalan->value)
                ->where('status', StatusDokumen::Issued->value)
                ->whereDate('tanggal', '>=', $dari)
                ->whereDate('tanggal', '<=', $sampai))
            ->sum('berat_netto_kg'), 2);

        $targetKg = round((float) config('wms.rendemen.target_bulanan_ton', 100.0) * 1000, 2);
        $wasteKg = round($totalPoy - $totalDty, 2);

        return [
            'totalPoy' => $totalPoy,
            'totalDty' => $totalDty,
            'rendemen' => self::persenRendemen($totalPoy, $totalDty),
            'waste' => self::persenWaste($totalPoy, $totalDty),
            'wasteKg' => $wasteKg,
            'targetKg' => $targetKg,
            'pencapaianTarget' => $targetKg > 0 ? round($totalDty / $targetKg * 100, 2) : null,
            'selisihTargetKg' => round($totalDty - $targetKg, 2),
            'surplus' => $totalDty > $totalPoy,
            'perMerge' => collect(),
        ];
    }

    public static function persenRendemen(float $poy, float $dty): ?float
    {
        if ($poy <= 0) {
            return null;
        }

        return round($dty / $poy * 100, 2);
    }

    public static function persenWaste(float $poy, float $dty): ?float
    {
        $rendemen = self::persenRendemen($poy, $dty);

        if ($rendemen === null) {
            return null;
        }

        return round(100 - $rendemen, 2);
    }
}
