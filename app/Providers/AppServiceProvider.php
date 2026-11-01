<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use App\Models\SupplierCustomer;
use App\Models\TransaksiMasuk;
use App\Policies\BarangPolicy;
use App\Policies\GradePolicy;
use App\Policies\KategoriBarangPolicy;
use App\Policies\KonversiSatuanPolicy;
use App\Policies\PenerimaanPolicy;
use App\Policies\SatuanPolicy;
use App\Policies\SupplierCustomerPolicy;
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
    }
}
