<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique();

            $table->enum('jenis_keluar', [
                'POY_PRODUKSI',
                'DTY_SURAT_JALAN',
                'DTY_SCRAP_REJECT',
                'ADJUSTMENT_OUT',
            ]);

            // Format: 244/DTY/PEI/X/2026 (lihat config/wms.php format_nomor).
            $table->string('nomor_surat_jalan', 50)->nullable()->unique();

            $table->date('tanggal');

            $table->foreignId('partner_id')->nullable()->constrained('supplier_customer')->nullOnDelete();

            // Data header Surat Jalan yang tercetak pada dokumen pengiriman.
            $table->string('no_kendaraan', 30)->nullable();
            $table->string('nama_sopir', 100)->nullable();
            $table->string('pic_pengirim', 100)->nullable();
            $table->string('pic_mengetahui', 100)->nullable();

            $table->tinyInteger('shift')->nullable();
            $table->string('operator_nama', 100)->nullable();

            // Wajib diisi untuk DTY_SCRAP_REJECT agar alasan scrap tercatat.
            $table->text('catatan')->nullable();

            $table->enum('status', ['DRAFT', 'POSTED', 'ISSUED', 'VOID', 'CANCELLED'])
                ->default('POSTED');

            $table->text('alasan_void')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();

            $table->index(['tanggal', 'jenis_keluar', 'status'], 'trx_keluar_laporan_index');
            $table->index(['jenis_keluar', 'shift'], 'trx_keluar_shift_index');
        });

        Schema::create('transaksi_keluar_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_keluar_id')->constrained('transaksi_keluar')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->string('merge', 50);
            $table->foreignId('grade_id')->constrained('grade')->restrictOnDelete();

            $table->decimal('jumlah_kemasan', 12, 2)->nullable();
            $table->foreignId('satuan_kemasan_id')->nullable()->constrained('satuan')->nullOnDelete();
            $table->integer('jumlah_bobbin')->nullable();

            $table->decimal('berat_netto_kg', 14, 2);

            // Berat gross hanya bermakna untuk DTY; barang lain cukup memakai netto.
            $table->decimal('berat_gross_kg', 14, 2)->nullable();

            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['barang_id', 'merge', 'grade_id'], 'trx_keluar_detail_stok_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_keluar_detail');
        Schema::dropIfExists('transaksi_keluar');
    }
};
