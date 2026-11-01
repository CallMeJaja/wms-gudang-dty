<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_customer', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->enum('jenis', ['SUPPLIER', 'CUSTOMER', 'BOTH', 'INTERNAL'])->index();
            $table->string('kontak', 100)->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('konversi_satuan', function (Blueprint $table) {
            $table->id();

            // NULL = berlaku global (bukan spesifik satu barang).
            $table->foreignId('barang_id')->nullable()->constrained('barang')->cascadeOnDelete();
            $table->foreignId('dari_satuan_id')->constrained('satuan')->restrictOnDelete();
            $table->foreignId('ke_satuan_id')->constrained('satuan')->restrictOnDelete();

            $table->decimal('faktor', 12, 4);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(
                ['barang_id', 'dari_satuan_id', 'ke_satuan_id'],
                'konversi_satuan_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konversi_satuan');
        Schema::dropIfExists('supplier_customer');
    }
};
