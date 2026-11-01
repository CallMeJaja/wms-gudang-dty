<?php

namespace App\Models;

use App\Enums\StatusDokumen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class StokBarang extends Model
{
    protected $table = 'stok_barang';

    protected $fillable = [
        'barang_id',
        'merge',
        'grade_id',
        'stok_aktif',
        'stok_buffer',
        'jumlah_bobbin',
        'jumlah_box',
    ];

    protected function casts(): array
    {
        return [
            'stok_aktif' => 'decimal:2',
            'stok_buffer' => 'decimal:2',
            'jumlah_bobbin' => 'integer',
            'jumlah_box' => 'integer',
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

    /*
    |--------------------------------------------------------------------------
    | Atribut Turunan (bukan kolom tersimpan — hindari data ganda)
    |--------------------------------------------------------------------------
    */

    /**
     * Total stok = aktif + buffer. Selalu dipakai untuk tampilan,
     * tetapi pengeluaran POY hanya boleh memotong stok_aktif.
     */
    protected function jumlahStok(): Attribute
    {
        return Attribute::get(
            fn (): float => (float) $this->stok_aktif + (float) $this->stok_buffer
        );
    }

    /**
     * Konversi stok ke jumlah pallet (threshold visual saja, PRD A2).
     * POY memakai pallet.poy_kg, DTY memakai pallet.dty_kg.
     */
    protected function stokPallet(): Attribute
    {
        return Attribute::get(function (): ?float {
            $kgPerPallet = $this->barang?->isDty()
                ? (float) config('wms.pallet.dty_kg')
                : (float) config('wms.pallet.poy_kg');

            if ($kgPerPallet <= 0) {
                return null;
            }

            return round($this->jumlah_stok / $kgPerPallet, 2);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopePosisi(Builder $query, int $barangId, string $merge, int $gradeId): Builder
    {
        return $query->where('barang_id', $barangId)
            ->where('merge', $merge)
            ->where('grade_id', $gradeId);
    }

    public function scopeHanyaMergeMinggu(Builder $query): Builder
    {
        return $query->whereHas('barang', fn (Builder $q) => $q->where('is_merge_minggu', true));
    }
}
