<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
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
