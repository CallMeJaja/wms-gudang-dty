<?php

namespace App\Models;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStok extends Model
{
    protected $table = 'mutasi_stok';

    protected $fillable = [
        'barang_id',
        'merge',
        'grade_id',
        'tanggal',
        'jenis_transaksi',
        'pos',
        'ref_nomor',
        'masuk_qty',
        'keluar_qty',
        'saldo_akhir_qty',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jenis_transaksi' => JenisMutasi::class,
            'pos' => PosStok::class,
            'masuk_qty' => 'decimal:2',
            'keluar_qty' => 'decimal:2',
            'saldo_akhir_qty' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeKartuStok(Builder $query, int $barangId, string $merge, int $gradeId): Builder
    {
        return $query->where('barang_id', $barangId)
            ->where('merge', $merge)
            ->where('grade_id', $gradeId)
            ->orderBy('tanggal')
            ->orderBy('id');
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

    /**
     * Delta bersih terhadap saldo pos: masuk dikurangi keluar.
     */
    public function deltaQty(): float
    {
        return (float) $this->masuk_qty - (float) $this->keluar_qty;
    }
}
