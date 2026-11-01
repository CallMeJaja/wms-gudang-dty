<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    protected $table = 'satuan';

    protected $fillable = ['kode', 'nama'];

    public const KG = 'KG';

    public const PCS = 'PCS';

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'satuan_dasar_id');
    }
}
