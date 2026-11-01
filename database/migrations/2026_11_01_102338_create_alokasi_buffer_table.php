<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alokasi_buffer', function (Blueprint $table) {
            $table->id();

            // Hari kerja alokasi (Senin–Sabtu).
            $table->date('tanggal');

            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->string('merge', 50);

            // Informatif saja: kuota buffer dihitung per (barang, merge, tanggal),
            // bukan per grade, agar tidak terjadi dobel alokasi dalam satu hari.
            $table->foreignId('grade_id')->nullable()->constrained('grade')->nullOnDelete();

            $table->decimal('jumlah_kg', 14, 2)->default(0);

            $table->foreignId('sumber_transaksi_masuk_id')
                ->nullable()
                ->constrained('transaksi_masuk')
                ->nullOnDelete();

            // true = pasokan < 7 pallet, buffer sengaja tidak disisihkan (A13).
            $table->boolean('dilewati')->default(false);

            $table->string('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['barang_id', 'merge', 'tanggal'], 'alokasi_buffer_unique');
            $table->index(['tanggal', 'dilewati']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_buffer');
    }
};
