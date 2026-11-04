<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\StokBarang;
use App\Models\User;
use Filament\Notifications\DatabaseNotification;
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
            $minimum = (float) $barang->stok_minimum;
            $total = (float) $barang->total_stok_aktif + (float) $barang->total_stok_buffer;

            if ($total + 1e-9 >= $minimum || $penerima->isEmpty()) {
                continue;
            }

            $satuan = $barang->satuanDasar?->kode ?? 'unit';
            $judul = "Stok {$barang->kode_barang} menipis";

            $notifikasi = Notification::make()
                ->title($judul)
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

            $this->kirimPerPenerima(
                $penerima,
                $judul,
                $notifikasi,
                sprintf('%s:barang:%d', self::PREFIX_CACHE, $barang->id),
                ['barang_id' => $barang->id],
            );
        }
    }

    private function periksaStokAktifPoy(Collection $penerima): void
    {
        $pallet = (int) config('wms.ambang_poy.min_stok_aktif_pallet');
        $kgPerPallet = (float) config('wms.pallet.poy_kg');
        $ambang = $pallet * $kgPerPallet;

        $stokAktif = (float) StokBarang::query()
            ->whereHas('barang', fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('kategori', fn (Builder $kategori) => $kategori->where('kode', KategoriBarang::POY)))
            ->sum('stok_aktif');

        if ($stokAktif + 1e-9 >= $ambang || $penerima->isEmpty()) {
            return;
        }

        $judul = 'Stok aktif POY kritis';

        $notifikasi = Notification::make()
            ->title($judul)
            ->body(sprintf(
                'Stok aktif %s kg di bawah kebutuhan %s kg (%d pallet).',
                $this->angka($stokAktif),
                $this->angka($ambang),
                $pallet,
            ))
            ->icon('heroicon-o-exclamation-triangle')
            ->danger()
            ->toDatabase();

        $this->kirimPerPenerima(
            $penerima,
            $judul,
            $notifikasi,
            self::PREFIX_CACHE.':poy-aktif',
            [],
        );
    }

    /**
     * @param  array<string, mixed>  $konteks
     */
    private function kirimPerPenerima(
        Collection $penerima,
        string $judul,
        DatabaseNotification $notifikasi,
        string $kunciDasar,
        array $konteks,
    ): void {
        foreach ($penerima as $user) {
            $kunci = sprintf('%s:user:%d', $kunciDasar, $user->id);

            if (! Cache::add($kunci, true, now()->addDay())) {
                continue;
            }

            $sudahAda = $user->notifications()
                ->where('data->title', $judul)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($sudahAda) {
                continue;
            }

            try {
                $user->notifyNow($notifikasi);
            } catch (Throwable $exception) {
                Cache::forget($kunci);

                Log::warning('Notifikasi stok menipis gagal dikirim.', array_merge($konteks, [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]));
            }
        }
    }

    private function angka(float $nilai): string
    {
        return number_format($nilai, 2, ',', '.');
    }
}
