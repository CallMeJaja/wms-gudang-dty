<?php

namespace App\Models;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiKeluar extends Model
{
    protected $table = 'transaksi_keluar';

    protected $fillable = [
        'nomor_transaksi',
        'jenis_keluar',
        'nomor_surat_jalan',
        'tanggal',
        'partner_id',
        'no_kendaraan',
        'nama_sopir',
        'pic_pengirim',
        'pic_mengetahui',
        'shift',
        'operator_nama',
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
            'jenis_keluar' => JenisKeluar::class,
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
        return $this->hasMany(TransaksiKeluarDetail::class);
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

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
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

    public function totalNettoKg(): float
    {
        return (float) $this->detail->sum('berat_netto_kg');
    }

    public function totalGrossKg(): float
    {
        return (float) $this->detail->sum('berat_gross_kg');
    }
}
