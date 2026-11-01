<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_opname', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_opname', 50)->unique();
            $table->date('tanggal');

            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->string('merge', 50);
            $table->foreignId('grade_id')->constrained('grade')->restrictOnDelete();

            $table->decimal('stok_sistem_qty', 14, 2);
            $table->decimal('stok_fisik_qty', 14, 2);

            $table->string('keterangan')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            // Dua FK terpisah agar asal penyesuaian tidak ambigu.
            $table->foreignId('transaksi_masuk_id')->nullable()->constrained('transaksi_masuk')->nullOnDelete();
            $table->foreignId('transaksi_keluar_id')->nullable()->constrained('transaksi_keluar')->nullOnDelete();

            $table->timestamps();

            $table->unique(['barang_id', 'merge', 'grade_id', 'tanggal'], 'stok_opname_unique');
        });

        // Generated STORED: selisih dihitung DB, tidak bisa drift.
        DB::statement(
            'ALTER TABLE `stok_opname`
             ADD COLUMN `selisih_qty` DECIMAL(14,2)
             AS (`stok_fisik_qty` - `stok_sistem_qty`) STORED'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_opname');
    }
};
