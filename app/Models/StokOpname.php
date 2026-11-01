<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokOpname extends Model
{
    protected $table = 'stok_opname';

    protected $fillable = [
        'nomor_opname',
        'tanggal',
        'barang_id',
        'merge',
        'grade_id',
        'stok_sistem_qty',
        'stok_fisik_qty',
        'keterangan',
        'created_by',
        'transaksi_masuk_id',
        'transaksi_keluar_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'stok_sistem_qty' => 'decimal:2',
            'stok_fisik_qty' => 'decimal:2',
            // Generated STORED oleh DB — hanya baca.
            'selisih_qty' => 'decimal:2',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaksiMasuk(): BelongsTo
    {
        return $this->belongsTo(TransaksiMasuk::class);
    }

    public function transaksiKeluar(): BelongsTo
    {
        return $this->belongsTo(TransaksiKeluar::class);
    }
}
