<?php

use App\Http\Controllers\Admin\LaporanExportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::redirect('/login', '/admin/login')->name('login');
Route::redirect('/dashboard', '/admin');

Route::middleware('auth')->prefix('admin/laporan')->name('admin.laporan.')->group(function () {
    Route::get('keluar-masuk/excel', [LaporanExportController::class, 'keluarMasukExcel'])->name('keluar-masuk.excel');
    Route::get('keluar-masuk/pdf', [LaporanExportController::class, 'keluarMasukPdf'])->name('keluar-masuk.pdf');
    Route::get('kartu-stok/excel', [LaporanExportController::class, 'kartuStokExcel'])->name('kartu-stok.excel');
    Route::get('kartu-stok/pdf', [LaporanExportController::class, 'kartuStokPdf'])->name('kartu-stok.pdf');
    Route::get('rendemen/excel', [LaporanExportController::class, 'rendemenExcel'])->name('rendemen.excel');
    Route::get('rendemen/pdf', [LaporanExportController::class, 'rendemenPdf'])->name('rendemen.pdf');
});
