<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->string('kode_material', 30)
                ->nullable()
                ->unique()
                ->after('kode_barang');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropUnique(['kode_material']);
            $table->dropColumn('kode_material');
        });
    }
};
