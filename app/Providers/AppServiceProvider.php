<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\StokOpname;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\BarangPolicy;
use App\Policies\GradePolicy;
use App\Policies\KategoriBarangPolicy;
use App\Policies\KonversiSatuanPolicy;
use App\Policies\PenerimaanPolicy;
use App\Policies\PengeluaranPolicy;
use App\Policies\SatuanPolicy;
use App\Policies\StokBarangPolicy;
use App\Policies\StokOpnamePolicy;
use App\Policies\SupplierCustomerPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Catat login/logout Filament ke activity_logs (legacy controller sudah dihapus).
        Event::listen(Login::class, function (Login $event) {
            ActivityLog::catat('LOGIN', 'AUTH', $event->user->id, "Login berhasil: {$event->user->username}");
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::catat('LOGOUT', 'AUTH', $event->user->id, "Logout: {$event->user->username}");
            }
        });

        // Pemetaan Policy master data (PRD §2 & F1).
        Gate::policy(Barang::class, BarangPolicy::class);
        Gate::policy(SupplierCustomer::class, SupplierCustomerPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(KonversiSatuan::class, KonversiSatuanPolicy::class);
        Gate::policy(KategoriBarang::class, KategoriBarangPolicy::class);
        Gate::policy(Satuan::class, SatuanPolicy::class);

        // Transaksi penerimaan (F2) — satu policy terpusat untuk POY,
        // Item Pendukung (Gate Pass), dan DTY hasil produksi.
        Gate::policy(TransaksiMasuk::class, PenerimaanPolicy::class);

        // Transaksi pengeluaran (F3) — satu policy terpusat untuk POY
        // Produksi, Surat Jalan DTY, dan Scrap/Reject DTY.
        Gate::policy(TransaksiKeluar::class, PengeluaranPolicy::class);

        // Stock opname (F6) — hanya Admin (PRD §2, §4.6).
        Gate::policy(StokOpname::class, StokOpnamePolicy::class);

        // Posisi stok — baca-saja untuk semua role aktif (F4).
        Gate::policy(StokBarang::class, StokBarangPolicy::class);

        // Manajemen pengguna & activity log (F5) — hanya Admin.
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
    }
}
