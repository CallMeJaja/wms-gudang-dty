<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_barang')->restrictOnDelete();
            $table->string('kode_barang', 50)->unique();
            $table->string('nama_barang', 150);
            $table->string('denier', 20)->nullable();
            $table->string('filament', 20)->nullable();
            $table->foreignId('satuan_dasar_id')->constrained('satuan')->restrictOnDelete();
            $table->decimal('stok_minimum', 14, 2)->nullable();

            // Menandai barang yang merge-nya boleh dialokasikan ke buffer Minggu.
            $table->boolean('is_merge_minggu')->default(false)->index();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
