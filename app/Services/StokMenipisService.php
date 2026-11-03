<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\StokBarang;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class StokMenipisService
{
    private const PREFIX_CACHE = 'wms:notifikasi-stok-menipis';

    public function periksa(?int $barangId = null): void
    {
        $penerima = User::query()
            ->where('is_active', true)
            ->whereIn('role', [Role::Admin->value, Role::Manajer->value])
            ->get();

        $this->periksaItem($barangId, $penerima);
        $this->periksaStokAktifPoy($penerima);
    }

    private function periksaItem(?int $barangId, Collection $penerima): void
    {
        $daftarBarang = Barang::query()
            ->aktif()
            ->whereNotNull('stok_minimum')
            ->when($barangId !== null, fn (Builder $query) => $query->whereKey($barangId))
            ->with('satuanDasar')
            ->withSum('stok as total_stok_aktif', 'stok_aktif')
            ->withSum('stok as total_stok_buffer', 'stok_buffer')
            ->get();

        foreach ($daftarBarang as $barang) {
            $kunci = sprintf('%s:barang:%d', self::PREFIX_CACHE, $barang->id);
            $minimum = (float) $barang->stok_minimum;
            $total = (float) $barang->total_stok_aktif + (float) $barang->total_stok_buffer;

            if ($total + 1e-9 >= $minimum) {
                Cache::forget($kunci);

                continue;
            }

            if ($penerima->isEmpty() || ! Cache::add($kunci, true)) {
                continue;
            }

            try {
                $satuan = $barang->satuanDasar?->kode ?? 'unit';

                $notifikasi = Notification::make()
                    ->title("Stok {$barang->kode_barang} menipis")
                    ->body(sprintf(
                        'Sisa %s %s dari minimum %s %s.',
                        $this->angka($total),
                        $satuan,
                        $this->angka($minimum),
                        $satuan,
                    ))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->warning()
                    ->toDatabase();

                foreach ($penerima as $user) {
                    $user->notifyNow($notifikasi);
                }
            } catch (Throwable $exception) {
                Cache::forget($kunci);

                Log::warning('Notifikasi stok menipis gagal dikirim.', [
                    'barang_id' => $barang->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function periksaStokAktifPoy(Collection $penerima): void
    {
        $kunci = self::PREFIX_CACHE.':poy-aktif';
        $pallet = (int) config('wms.ambang_poy.min_stok_aktif_pallet');
        $kgPerPallet = (float) config('wms.pallet.poy_kg');
        $ambang = $pallet * $kgPerPallet;

        $stokAktif = (float) StokBarang::query()
            ->whereHas('barang', fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('kategori', fn (Builder $kategori) => $kategori->where('kode', KategoriBarang::POY)))
            ->sum('stok_aktif');

        if ($stokAktif + 1e-9 >= $ambang) {
            Cache::forget($kunci);

            return;
        }

        if ($penerima->isEmpty() || ! Cache::add($kunci, true)) {
            return;
        }

        try {
            $notifikasi = Notification::make()
                ->title('Stok aktif POY kritis')
                ->body(sprintf(
                    'Stok aktif %s kg di bawah kebutuhan %s kg (%d pallet).',
                    $this->angka($stokAktif),
                    $this->angka($ambang),
                    $pallet,
                ))
                ->icon('heroicon-o-exclamation-triangle')
                ->danger()
                ->toDatabase();

            foreach ($penerima as $user) {
                $user->notifyNow($notifikasi);
            }
        } catch (Throwable $exception) {
            Cache::forget($kunci);

            Log::warning('Notifikasi stok aktif POY gagal dikirim.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function angka(float $nilai): string
    {
        return number_format($nilai, 2, ',', '.');
    }
}
