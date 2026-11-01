<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique();

            $table->enum('jenis_masuk', [
                'POY_IPCI',
                'PENDUKUNG_GATEPASS',
                'DTY_PRODUKSI',
                'ADJUSTMENT_IN',
            ]);

            // Dokumen resmi Gate Pass (lifecycle DRAFT -> ISSUED -> CANCELLED).
            $table->string('nomor_gate_pass', 50)->nullable()->unique();

            // Tanggal bisnis (bukan timestamp) — lihat A10.
            $table->date('tanggal');

            $table->foreignId('partner_id')->nullable()->constrained('supplier_customer')->nullOnDelete();

            // Untuk partner internal, mis. 'AREA PRODUKSI' / 'AREA DOFFING'.
            $table->string('sumber_internal', 100)->nullable();
            $table->string('ref_dokumen', 100)->nullable();

            $table->tinyInteger('shift')->nullable();
            $table->string('operator_nama', 100)->nullable();
            $table->string('merge_poy_asal', 50)->nullable();

            $table->string('pengirim', 100)->nullable();
            $table->string('penerima', 100)->nullable();
            $table->text('catatan')->nullable();

            $table->enum('status', ['DRAFT', 'POSTED', 'ISSUED', 'VOID', 'CANCELLED'])
                ->default('POSTED');

            $table->text('alasan_void')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();

            // Index laporan keluar-masuk (PRD §6.1).
            $table->index(['tanggal', 'jenis_masuk', 'status'], 'trx_masuk_laporan_index');
            $table->index(['jenis_masuk', 'merge_poy_asal'], 'trx_masuk_merge_asal_index');
        });

        Schema::create('transaksi_masuk_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_masuk_id')->constrained('transaksi_masuk')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->string('merge', 50);
            $table->foreignId('grade_id')->constrained('grade')->restrictOnDelete();

            $table->decimal('jumlah_kemasan', 12, 2)->nullable();
            $table->foreignId('satuan_kemasan_id')->nullable()->constrained('satuan')->nullOnDelete();
            $table->integer('jumlah_bobbin')->nullable();

            // Selalu diisi dari Net Weight hasil timbangan / dokumen.
            $table->decimal('berat_kg', 14, 2);

            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['barang_id', 'merge', 'grade_id'], 'trx_masuk_detail_stok_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_masuk_detail');
        Schema::dropIfExists('transaksi_masuk');
    }
};
