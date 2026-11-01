<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_stok', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->string('merge', 50);
            $table->foreignId('grade_id')->constrained('grade')->restrictOnDelete();

            $table->date('tanggal');

            $table->enum('jenis_transaksi', [
                'MASUK',
                'KELUAR',
                'ADJUSTMENT_IN',
                'ADJUSTMENT_OUT',
                'ALOKASI_BUFFER',
                'RILIS_BUFFER',
                'VOID',
            ]);

            // Pos stok yang terdampak oleh baris mutasi ini.
            $table->enum('pos', ['AKTIF', 'BUFFER']);

            $table->string('ref_nomor', 50);

            $table->decimal('masuk_qty', 14, 2)->default(0);
            $table->decimal('keluar_qty', 14, 2)->default(0);

            // Snapshot audit saldo pada pos tersebut (bukan sumber kebenaran).
            $table->decimal('saldo_akhir_qty', 14, 2);

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Kartu stok: per barang/merge/grade, urut tanggal.
            $table->index(['barang_id', 'merge', 'grade_id', 'tanggal'], 'mutasi_stok_kartu_index');

            // Laporan keluar-masuk & rekonsiliasi.
            $table->index(['tanggal', 'jenis_transaksi'], 'mutasi_stok_laporan_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_stok');
    }
};
