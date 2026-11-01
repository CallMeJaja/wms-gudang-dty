<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierCustomer extends Model
{
    use SoftDeletes;

    protected $table = 'supplier_customer';

    protected $fillable = ['kode', 'nama', 'jenis', 'kontak', 'alamat'];

    public const JENIS = ['SUPPLIER', 'CUSTOMER', 'BOTH', 'INTERNAL'];

    public const JENIS_LABEL = [
        'SUPPLIER' => 'Pemasok',
        'CUSTOMER' => 'Pelanggan',
        'BOTH' => 'Keduanya',
        'INTERNAL' => 'Internal',
    ];

    public static function labelJenis(?string $jenis): string
    {
        return self::JENIS_LABEL[$jenis] ?? (string) $jenis;
    }

    public function transaksiMasuk(): HasMany
    {
        return $this->hasMany(TransaksiMasuk::class, 'partner_id');
    }

    public function transaksiKeluar(): HasMany
    {
        return $this->hasMany(TransaksiKeluar::class, 'partner_id');
    }
}
