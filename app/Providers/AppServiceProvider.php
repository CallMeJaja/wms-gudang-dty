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
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Catat login/logout Filament ke activity_logs.
        Event::listen(Login::class, function (Login $event) {
            ActivityLog::catat('LOGIN', 'AUTH', $event->user->id, "Login berhasil: {$event->user->username}");
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::catat('LOGOUT', 'AUTH', $event->user->id, "Logout: {$event->user->username}");
            }
        });

        // Pemetaan policy master data.
        Gate::policy(Barang::class, BarangPolicy::class);
        Gate::policy(SupplierCustomer::class, SupplierCustomerPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(KonversiSatuan::class, KonversiSatuanPolicy::class);
        Gate::policy(KategoriBarang::class, KategoriBarangPolicy::class);
        Gate::policy(Satuan::class, SatuanPolicy::class);

        // Satu policy penerimaan untuk POY, Item Pendukung (Gate Pass), dan DTY hasil produksi.
        Gate::policy(TransaksiMasuk::class, PenerimaanPolicy::class);

        // Satu policy pengeluaran untuk POY Produksi, Surat Jalan DTY, dan Scrap/Reject DTY.
        Gate::policy(TransaksiKeluar::class, PengeluaranPolicy::class);

        // Stock opname — hanya Admin.
        Gate::policy(StokOpname::class, StokOpnamePolicy::class);

        // Posisi stok — baca-saja untuk semua role aktif.
        Gate::policy(StokBarang::class, StokBarangPolicy::class);

        // Manajemen pengguna & activity log — hanya Admin.
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
    }
}
