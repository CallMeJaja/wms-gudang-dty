<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBarang extends Model
{
    protected $table = 'kategori_barang';

    protected $fillable = ['kode', 'nama', 'keterangan'];

    public const POY = 'POY';

    public const DTY = 'DTY';

    public const PENDUKUNG = 'PENDUKUNG';

    public function grade(): HasMany
    {
        return $this->hasMany(Grade::class, 'kategori_id');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
