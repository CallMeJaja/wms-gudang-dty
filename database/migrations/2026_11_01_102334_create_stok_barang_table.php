<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_barang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->string('merge', 50);

            // Kolom ini hanya mengikat grade secara umum. Kesesuaian grade
            // dengan kategori barang divalidasi di service, bukan oleh FK.
            $table->foreignId('grade_id')->constrained('grade')->restrictOnDelete();

            // Satuan mengikuti barang.satuan_dasar_id:
            // KG untuk POY/DTY/Oli, PCS untuk Paper Tube/Box/Pallet.
            $table->decimal('stok_aktif', 14, 2)->default(0);
            $table->decimal('stok_buffer', 14, 2)->default(0);

            $table->integer('jumlah_bobbin')->default(0);
            $table->integer('jumlah_box')->default(0);

            $table->timestamps();

            // Satu kombinasi barang, merge, dan grade hanya boleh punya satu baris stok.
            $table->unique(['barang_id', 'merge', 'grade_id'], 'stok_barang_unique');
        });

        // Integritas DB: tidak ada saldo negatif pada kedua pos.
        // (MariaDB 13 & MySQL 8 sama-sama menegakkan CHECK.)
        DB::statement(
            'ALTER TABLE `stok_barang` ADD CONSTRAINT `stok_barang_non_negatif`
             CHECK (`stok_aktif` >= 0 AND `stok_buffer` >= 0)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_barang');
    }
};
