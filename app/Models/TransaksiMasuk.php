<?php

namespace App\Models;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiMasuk extends Model
{
    protected $table = 'transaksi_masuk';

    protected $fillable = [
        'nomor_transaksi',
        'jenis_masuk',
        'nomor_gate_pass',
        'tanggal',
        'partner_id',
        'sumber_internal',
        'ref_dokumen',
        'shift',
        'operator_nama',
        'merge_poy_asal',
        'pengirim',
        'penerima',
        'catatan',
        'status',
        'alasan_void',
        'voided_at',
        'voided_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis_masuk' => JenisMasuk::class,
            'status' => StatusDokumen::class,
            'tanggal' => 'date',
            'voided_at' => 'datetime',
            'shift' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function detail(): HasMany
    {
        return $this->hasMany(TransaksiMasukDetail::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(SupplierCustomer::class, 'partner_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pemvoid(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function alokasiBuffer(): HasMany
    {
        return $this->hasMany(AlokasiBuffer::class, 'sumber_transaksi_masuk_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Hanya transaksi yang benar-benar mempengaruhi stok.
     * Draft tidak mengubah saldo.
     */
    public function scopeMempengaruhiStok(Builder $query): Builder
    {
        return $query->whereIn('status', [StatusDokumen::Posted->value, StatusDokumen::Issued->value]);
    }

    public function scopePeriode(Builder $query, $awal, $akhir): Builder
    {
        return $query->whereBetween('tanggal', [$awal, $akhir]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function totalBeratKg(): float
    {
        return (float) $this->detail->sum('berat_kg');
    }
}
