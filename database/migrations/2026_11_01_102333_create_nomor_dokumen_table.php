<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_dokumen', function (Blueprint $table) {
            $table->id();

            $table->enum('jenis', ['SURAT_JALAN_DTY', 'GATE_PASS', 'TRX_MASUK', 'TRX_KELUAR']);

            // 'YYYY-MM' untuk dokumen bulanan, 'YYYY-MM-DD' untuk harian.
            $table->string('periode', 10);

            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['jenis', 'periode'], 'nomor_dokumen_jenis_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_dokumen');
    }
};
