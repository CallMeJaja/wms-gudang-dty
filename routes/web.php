<?php

use App\Http\Controllers\Admin\LaporanExportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inbound\PenerimaanDtyController;
use App\Http\Controllers\Inbound\PenerimaanPendukungController;
use App\Http\Controllers\Inbound\PenerimaanPoyController;
use App\Http\Controllers\Laporan\KartuStokController;
use App\Http\Controllers\Laporan\KeluarMasukController;
use App\Http\Controllers\Laporan\RendemenController;
use App\Http\Controllers\Master\BarangController;
use App\Http\Controllers\Master\GradeController;
use App\Http\Controllers\Master\KategoriController;
use App\Http\Controllers\Master\KonversiController;
use App\Http\Controllers\Master\PartnerController;
use App\Http\Controllers\Master\SatuanController;
use App\Http\Controllers\Outbound\PengeluaranPoyController;
use App\Http\Controllers\Outbound\ScrapController;
use App\Http\Controllers\Outbound\SuratJalanController;
use App\Http\Controllers\StokOpnameController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

/*
|--------------------------------------------------------------------------
| Guest (belum login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Terautentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/rilis-buffer', [DashboardController::class, 'rilis'])
        ->middleware('role:admin,operator')
        ->name('dashboard.rilis-buffer');
});

/*
|--------------------------------------------------------------------------
| Transaksi Masuk / Inbound (F2) — RBAC
|--------------------------------------------------------------------------
|
| Sesuai PRD §2: Operator HARUS bisa input transaksi penerimaan, sehingga
| route tulis memakai `role:admin,operator` (BUKAN `role:admin`).
|
| - `tolak.manajer` memblokir semua method tulis untuk Manajer (read-only):
|   create POST/PUT/DELETE + aksi posting/issue. Halaman GET (index/show)
|   tetap dibuka untuk semua role yang login.
| - Otorisasi detail (DRAFT-only, jenis transaksi cocok) ada di
|   PenerimaanPolicy lewat Gate::authorize di controller.
|
*/
Route::middleware(['auth', 'tolak.manajer'])->prefix('transaksi/masuk')->name('inbound.')->group(function () {
    // Operator & Admin boleh menulis transaksi penerimaan.
    $tulis = ['role:admin,operator'];
    // Void: HANYA Admin (PRD §2) — F6.
    $void = ['role:admin'];

    // ----- Penerimaan POY dari IPCI (DRAFT -> POSTED) -----
    Route::prefix('poy')->name('poy.')->group(function () use ($tulis, $void) {
        Route::get('/', [PenerimaanPoyController::class, 'index'])->name('index');
        Route::get('create', [PenerimaanPoyController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [PenerimaanPoyController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{poy}', [PenerimaanPoyController::class, 'show'])->name('show');
        Route::get('{poy}/edit', [PenerimaanPoyController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{poy}', [PenerimaanPoyController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{poy}', [PenerimaanPoyController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{poy}/posting', [PenerimaanPoyController::class, 'posting'])->middleware($tulis)->name('posting');
        Route::post('{poy}/void', [PenerimaanPoyController::class, 'void'])->middleware($void)->name('void');
    });

    // ----- Penerimaan Item Pendukung / Gate Pass (DRAFT -> ISSUED) -----
    Route::prefix('pendukung')->name('pendukung.')->group(function () use ($tulis, $void) {
        Route::get('/', [PenerimaanPendukungController::class, 'index'])->name('index');
        Route::get('create', [PenerimaanPendukungController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [PenerimaanPendukungController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{pendukung}', [PenerimaanPendukungController::class, 'show'])->name('show');
        Route::get('{pendukung}/edit', [PenerimaanPendukungController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{pendukung}', [PenerimaanPendukungController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{pendukung}', [PenerimaanPendukungController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{pendukung}/issue', [PenerimaanPendukungController::class, 'issue'])->middleware($tulis)->name('issue');
        Route::post('{pendukung}/void', [PenerimaanPendukungController::class, 'void'])->middleware($void)->name('void');
    });

    // ----- Penerimaan DTY dari Produksi (DRAFT -> POSTED) -----
    Route::prefix('dty')->name('dty.')->group(function () use ($tulis, $void) {
        // Pratinjau harus dideklarasikan SEBELUM {dty} agar tidak tertangkap
        // sebagai parameter route.
        Route::get('pratinjau/autofill', [PenerimaanDtyController::class, 'pratinjau'])->name('pratinjau');
        Route::get('pratinjau/stok', [PenerimaanDtyController::class, 'stokRingkas'])->name('stok');

        Route::get('/', [PenerimaanDtyController::class, 'index'])->name('index');
        Route::get('create', [PenerimaanDtyController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [PenerimaanDtyController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{dty}', [PenerimaanDtyController::class, 'show'])->name('show');
        Route::get('{dty}/edit', [PenerimaanDtyController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{dty}', [PenerimaanDtyController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{dty}', [PenerimaanDtyController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{dty}/posting', [PenerimaanDtyController::class, 'posting'])->middleware($tulis)->name('posting');
        Route::post('{dty}/void', [PenerimaanDtyController::class, 'void'])->middleware($void)->name('void');
    });
});

/*
|--------------------------------------------------------------------------
| Transaksi Keluar / Outbound (F3) — RBAC
|--------------------------------------------------------------------------
|
| Sama seperti inbound: Operator HARUS bisa input transaksi pengeluaran,
| route tulis memakai `role:admin,operator`. Void HANYA Admin.
|
*/
Route::middleware(['auth', 'tolak.manajer'])->prefix('transaksi/keluar')->name('outbound.')->group(function () {
    $tulis = ['role:admin,operator'];
    $void = ['role:admin'];

    // ----- Pengeluaran POY ke Produksi (DRAFT -> POSTED) -----
    Route::prefix('poy')->name('poy.')->group(function () use ($tulis, $void) {
        Route::get('/', [PengeluaranPoyController::class, 'index'])->name('index');
        Route::get('create', [PengeluaranPoyController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [PengeluaranPoyController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{poy}', [PengeluaranPoyController::class, 'show'])->name('show');
        Route::get('{poy}/edit', [PengeluaranPoyController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{poy}', [PengeluaranPoyController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{poy}', [PengeluaranPoyController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{poy}/posting', [PengeluaranPoyController::class, 'posting'])->middleware($tulis)->name('posting');
        Route::post('{poy}/void', [PengeluaranPoyController::class, 'void'])->middleware($void)->name('void');
    });

    // ----- Surat Jalan DTY (DRAFT -> ISSUED + cetak) -----
    Route::prefix('surat-jalan')->name('surat-jalan.')->group(function () use ($tulis, $void) {
        Route::get('/', [SuratJalanController::class, 'index'])->name('index');
        Route::get('create', [SuratJalanController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [SuratJalanController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{suratJalan}', [SuratJalanController::class, 'show'])->name('show');
        Route::get('{suratJalan}/cetak', [SuratJalanController::class, 'cetak'])->name('cetak');
        Route::get('{suratJalan}/edit', [SuratJalanController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{suratJalan}', [SuratJalanController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{suratJalan}', [SuratJalanController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{suratJalan}/issue', [SuratJalanController::class, 'issue'])->middleware($tulis)->name('issue');
        Route::post('{suratJalan}/void', [SuratJalanController::class, 'void'])->middleware($void)->name('void');
    });

    // ----- Pengeluaran DTY Scrap/Reject (DRAFT -> POSTED) -----
    Route::prefix('scrap')->name('scrap.')->group(function () use ($tulis, $void) {
        Route::get('/', [ScrapController::class, 'index'])->name('index');
        Route::get('create', [ScrapController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [ScrapController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{scrap}', [ScrapController::class, 'show'])->name('show');
        Route::get('{scrap}/edit', [ScrapController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{scrap}', [ScrapController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{scrap}', [ScrapController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{scrap}/posting', [ScrapController::class, 'posting'])->middleware($tulis)->name('posting');
        Route::post('{scrap}/void', [ScrapController::class, 'void'])->middleware($void)->name('void');
    });
});

/*
|--------------------------------------------------------------------------
| Laporan & Cetak (F5) — semua role boleh baca (GET saja, tanpa tolak.manajer)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('keluar-masuk', [KeluarMasukController::class, 'index'])->name('keluar-masuk.index');
    Route::get('keluar-masuk/excel', [KeluarMasukController::class, 'excel'])->name('keluar-masuk.excel');
    Route::get('keluar-masuk/pdf', [KeluarMasukController::class, 'pdf'])->name('keluar-masuk.pdf');

    Route::get('kartu-stok', [KartuStokController::class, 'index'])->name('kartu-stok.index');
    Route::get('kartu-stok/excel', [KartuStokController::class, 'excel'])->name('kartu-stok.excel');
    Route::get('kartu-stok/pdf', [KartuStokController::class, 'pdf'])->name('kartu-stok.pdf');

    Route::get('rendemen', [RendemenController::class, 'index'])->name('rendemen.index');
    Route::get('rendemen/excel', [RendemenController::class, 'excel'])->name('rendemen.excel');
    Route::get('rendemen/pdf', [RendemenController::class, 'pdf'])->name('rendemen.pdf');
});

/*
|--------------------------------------------------------------------------
| Stock Opname (F6) — HANYA Admin (PRD §2, §4.6)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('opname')->name('opname.')->group(function () {
    Route::get('/', [StokOpnameController::class, 'index'])->name('index');
    Route::get('create', [StokOpnameController::class, 'create'])->name('create');
    Route::post('/', [StokOpnameController::class, 'store'])->name('store');
    Route::get('{opname}', [StokOpnameController::class, 'show'])->name('show');
});

// Cetak Gate Pass: halaman cetak format fisik, semua role boleh (GET).
Route::middleware('auth')->get(
    'transaksi/masuk/pendukung/{pendukung}/cetak',
    [PenerimaanPendukungController::class, 'cetak']
)->name('inbound.pendukung.cetak');

/*
|--------------------------------------------------------------------------
| Master Data (F1) — RBAC
|--------------------------------------------------------------------------
|
| - `tolak.manajer` memblokir semua non-GET untuk Manajer (read-only).
| - `role:admin` dipasang HANYA pada route tulis, sehingga Operator
|   maupun Manajer tetap bisa membuka index (GET).
|
*/
Route::middleware(['auth', 'tolak.manajer'])->prefix('master')->name('master.')->group(function () {
    // Opsi penulisan: hanya Admin.
    $hanyaAdmin = ['role:admin'];

    // ----- Barang -----
    Route::get('barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('barang/create', [BarangController::class, 'create'])->middleware($hanyaAdmin)->name('barang.create');
    Route::post('barang', [BarangController::class, 'store'])->middleware($hanyaAdmin)->name('barang.store');
    Route::get('barang/{barang}/edit', [BarangController::class, 'edit'])->middleware($hanyaAdmin)->name('barang.edit');
    Route::put('barang/{barang}', [BarangController::class, 'update'])->middleware($hanyaAdmin)->name('barang.update');
    Route::delete('barang/{barang}', [BarangController::class, 'destroy'])->middleware($hanyaAdmin)->name('barang.destroy');

    // ----- Partner (Supplier/Customer) -----
    Route::get('partner', [PartnerController::class, 'index'])->name('partner.index');
    Route::get('partner/create', [PartnerController::class, 'create'])->middleware($hanyaAdmin)->name('partner.create');
    Route::post('partner', [PartnerController::class, 'store'])->middleware($hanyaAdmin)->name('partner.store');
    Route::get('partner/{partner}/edit', [PartnerController::class, 'edit'])->middleware($hanyaAdmin)->name('partner.edit');
    Route::put('partner/{partner}', [PartnerController::class, 'update'])->middleware($hanyaAdmin)->name('partner.update');
    Route::delete('partner/{partner}', [PartnerController::class, 'destroy'])->middleware($hanyaAdmin)->name('partner.destroy');

    // ----- Grade -----
    Route::get('grade', [GradeController::class, 'index'])->name('grade.index');
    Route::get('grade/create', [GradeController::class, 'create'])->middleware($hanyaAdmin)->name('grade.create');
    Route::post('grade', [GradeController::class, 'store'])->middleware($hanyaAdmin)->name('grade.store');
    Route::get('grade/{grade}/edit', [GradeController::class, 'edit'])->middleware($hanyaAdmin)->name('grade.edit');
    Route::put('grade/{grade}', [GradeController::class, 'update'])->middleware($hanyaAdmin)->name('grade.update');
    Route::delete('grade/{grade}', [GradeController::class, 'destroy'])->middleware($hanyaAdmin)->name('grade.destroy');

    // ----- Konversi Satuan -----
    Route::get('konversi', [KonversiController::class, 'index'])->name('konversi.index');
    Route::get('konversi/create', [KonversiController::class, 'create'])->middleware($hanyaAdmin)->name('konversi.create');
    Route::post('konversi', [KonversiController::class, 'store'])->middleware($hanyaAdmin)->name('konversi.store');
    Route::get('konversi/{konversi}/edit', [KonversiController::class, 'edit'])->middleware($hanyaAdmin)->name('konversi.edit');
    Route::put('konversi/{konversi}', [KonversiController::class, 'update'])->middleware($hanyaAdmin)->name('konversi.update');
    Route::delete('konversi/{konversi}', [KonversiController::class, 'destroy'])->middleware($hanyaAdmin)->name('konversi.destroy');

    // ----- Kategori Barang -----
    Route::get('kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::get('kategori/create', [KategoriController::class, 'create'])->middleware($hanyaAdmin)->name('kategori.create');
    Route::post('kategori', [KategoriController::class, 'store'])->middleware($hanyaAdmin)->name('kategori.store');
    Route::get('kategori/{kategori}/edit', [KategoriController::class, 'edit'])->middleware($hanyaAdmin)->name('kategori.edit');
    Route::put('kategori/{kategori}', [KategoriController::class, 'update'])->middleware($hanyaAdmin)->name('kategori.update');
    Route::delete('kategori/{kategori}', [KategoriController::class, 'destroy'])->middleware($hanyaAdmin)->name('kategori.destroy');
});

/*
|--------------------------------------------------------------------------
| Master Data — Satuan (di luar prefix /master)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tolak.manajer'])->prefix('satuan')->name('master.satuan.')->group(function () {
    Route::get('/', [SatuanController::class, 'index'])->name('index');
    Route::get('create', [SatuanController::class, 'create'])->middleware('role:admin')->name('create');
    Route::post('/', [SatuanController::class, 'store'])->middleware('role:admin')->name('store');
    Route::get('{satuan}/edit', [SatuanController::class, 'edit'])->middleware('role:admin')->name('edit');
    Route::put('{satuan}', [SatuanController::class, 'update'])->middleware('role:admin')->name('update');
    Route::delete('{satuan}', [SatuanController::class, 'destroy'])->middleware('role:admin')->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Export laporan (Filament) — endpoint baru, UI legacy tetap ada sampai cutover
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin/laporan')->name('admin.laporan.')->group(function () {
    Route::get('keluar-masuk/excel', [LaporanExportController::class, 'keluarMasukExcel'])->name('keluar-masuk.excel');
    Route::get('keluar-masuk/pdf', [LaporanExportController::class, 'keluarMasukPdf'])->name('keluar-masuk.pdf');
    Route::get('kartu-stok/excel', [LaporanExportController::class, 'kartuStokExcel'])->name('kartu-stok.excel');
    Route::get('kartu-stok/pdf', [LaporanExportController::class, 'kartuStokPdf'])->name('kartu-stok.pdf');
    Route::get('rendemen/excel', [LaporanExportController::class, 'rendemenExcel'])->name('rendemen.excel');
    Route::get('rendemen/pdf', [LaporanExportController::class, 'rendemenPdf'])->name('rendemen.pdf');
});
