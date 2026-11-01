<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inbound\PenerimaanDtyController;
use App\Http\Controllers\Inbound\PenerimaanPendukungController;
use App\Http\Controllers\Inbound\PenerimaanPoyController;
use App\Http\Controllers\Master\BarangController;
use App\Http\Controllers\Master\GradeController;
use App\Http\Controllers\Master\KategoriController;
use App\Http\Controllers\Master\KonversiController;
use App\Http\Controllers\Master\PartnerController;
use App\Http\Controllers\Master\SatuanController;
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

    // ----- Penerimaan POY dari IPCI (DRAFT -> POSTED) -----
    Route::prefix('poy')->name('poy.')->group(function () use ($tulis) {
        Route::get('/', [PenerimaanPoyController::class, 'index'])->name('index');
        Route::get('create', [PenerimaanPoyController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [PenerimaanPoyController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{poy}', [PenerimaanPoyController::class, 'show'])->name('show');
        Route::get('{poy}/edit', [PenerimaanPoyController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{poy}', [PenerimaanPoyController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{poy}', [PenerimaanPoyController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{poy}/posting', [PenerimaanPoyController::class, 'posting'])->middleware($tulis)->name('posting');
    });

    // ----- Penerimaan Item Pendukung / Gate Pass (DRAFT -> ISSUED) -----
    Route::prefix('pendukung')->name('pendukung.')->group(function () use ($tulis) {
        Route::get('/', [PenerimaanPendukungController::class, 'index'])->name('index');
        Route::get('create', [PenerimaanPendukungController::class, 'create'])->middleware($tulis)->name('create');
        Route::post('/', [PenerimaanPendukungController::class, 'store'])->middleware($tulis)->name('store');
        Route::get('{pendukung}', [PenerimaanPendukungController::class, 'show'])->name('show');
        Route::get('{pendukung}/edit', [PenerimaanPendukungController::class, 'edit'])->middleware($tulis)->name('edit');
        Route::put('{pendukung}', [PenerimaanPendukungController::class, 'update'])->middleware($tulis)->name('update');
        Route::delete('{pendukung}', [PenerimaanPendukungController::class, 'destroy'])->middleware($tulis)->name('destroy');
        Route::post('{pendukung}/issue', [PenerimaanPendukungController::class, 'issue'])->middleware($tulis)->name('issue');
    });

    // ----- Penerimaan DTY dari Produksi (DRAFT -> POSTED) -----
    Route::prefix('dty')->name('dty.')->group(function () use ($tulis) {
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
    });
});

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
