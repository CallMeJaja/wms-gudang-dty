<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiKeluarDetail extends Model
{
    protected $table = 'transaksi_keluar_detail';

    protected $fillable = [
        'transaksi_keluar_id',
        'barang_id',
        'merge',
        'grade_id',
        'jumlah_kemasan',
        'satuan_kemasan_id',
        'jumlah_bobbin',
        'berat_netto_kg',
        'berat_gross_kg',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_kemasan' => 'decimal:2',
            'jumlah_bobbin' => 'integer',
            'berat_netto_kg' => 'decimal:2',
            'berat_gross_kg' => 'decimal:2',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiKeluar::class, 'transaksi_keluar_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function satuanKemasan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_kemasan_id');
    }
}
