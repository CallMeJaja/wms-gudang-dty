<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10);
            $table->foreignId('kategori_id')->constrained('kategori_barang')->restrictOnDelete();
            $table->string('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // A7: grade bersifat tertutup & unik per kategori.
            $table->unique(['kode', 'kategori_id'], 'grade_kode_kategori_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade');
    }
};
