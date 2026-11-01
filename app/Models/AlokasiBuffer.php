<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiBuffer extends Model
{
    protected $table = 'alokasi_buffer';

    protected $fillable = [
        'tanggal',
        'barang_id',
        'merge',
        'grade_id',
        'jumlah_kg',
        'sumber_transaksi_masuk_id',
        'dilewati',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah_kg' => 'decimal:2',
            'dilewati' => 'boolean',
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

    public function sumberTransaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiMasuk::class, 'sumber_transaksi_masuk_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
