<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonversiSatuan extends Model
{
    protected $table = 'konversi_satuan';

    protected $fillable = [
        'barang_id',
        'dari_satuan_id',
        'ke_satuan_id',
        'faktor',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'faktor' => 'decimal:4',
            'is_default' => 'boolean',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function dariSatuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'dari_satuan_id');
    }

    public function keSatuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'ke_satuan_id');
    }
}
