<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use SoftDeletes;

    protected $table = 'barang';

    protected $fillable = [
        'kategori_id',
        'kode_barang',
        'nama_barang',
        'denier',
        'filament',
        'satuan_dasar_id',
        'stok_minimum',
        'is_merge_minggu',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stok_minimum' => 'decimal:2',
            'is_merge_minggu' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }

    public function satuanDasar(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_dasar_id');
    }

    public function stok(): HasMany
    {
        return $this->hasMany(StokBarang::class);
    }

    public function konversi(): HasMany
    {
        return $this->hasMany(KonversiSatuan::class);
    }

    public function mutasiStok(): HasMany
    {
        return $this->hasMany(MutasiStok::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Label denier/filament, mis. "250/48". Null bila tidak relevan
     * (item pendukung biasanya tidak punya D/F).
     */
    public function getDenierFilamentAttribute(): ?string
    {
        if (! $this->denier || ! $this->filament) {
            return null;
        }

        return "{$this->denier}/{$this->filament}";
    }

    public function isPoy(): bool
    {
        return $this->kategori?->kode === KategoriBarang::POY;
    }

    public function isDty(): bool
    {
        return $this->kategori?->kode === KategoriBarang::DTY;
    }

    public function isPendukung(): bool
    {
        return $this->kategori?->kode === KategoriBarang::PENDUKUNG;
    }

    /**
     * Item pendukung & DTY memakai satuan PCS/KG; hanya POY yang
     * memakai pemisahan stok aktif vs buffer.
     */
    public function memakaiBuffer(): bool
    {
        return $this->isPoy();
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMergeMinggu(Builder $query): Builder
    {
        return $query->where('is_merge_minggu', true);
    }

    public function scopeKategori(Builder $query, string|int $kategori): Builder
    {
        return is_int($kategori)
            ? $query->where('kategori_id', $kategori)
            : $query->whereHas('kategori', fn (Builder $q) => $q->where('kode', $kategori));
    }
}
