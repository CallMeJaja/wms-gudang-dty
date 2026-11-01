<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiMasukDetail extends Model
{
    protected $table = 'transaksi_masuk_detail';

    protected $fillable = [
        'transaksi_masuk_id',
        'barang_id',
        'merge',
        'grade_id',
        'jumlah_kemasan',
        'satuan_kemasan_id',
        'jumlah_bobbin',
        'berat_kg',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_kemasan' => 'decimal:2',
            'jumlah_bobbin' => 'integer',
            'berat_kg' => 'decimal:2',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiMasuk::class, 'transaksi_masuk_id');
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

    /*
    |--------------------------------------------------------------------------
    | Penanda Gross pada Keterangan (DTY)
    |--------------------------------------------------------------------------
    |
    | Skema `transaksi_masuk_detail` (migrasi F0, tidak diubah pada F2B) hanya
    | memiliki `berat_kg`; kolom `berat_gross_kg` ada di detail keluar. Agar
    | gross DTY tetap tercatat & bisa divalidasi ulang tanpa mengubah skema,
    | nilai disimpan di kolom `keterangan` dengan penanda:
    |
    |     [GROSS=35.74] batch / catatan bebas
    |
    | Penanda selalu di depan sehingga `batch` (Gate Pass) tetap terbaca.
    */

    /**
     * Format penanda gross: [GROSS=35.74]
     */
    public static function penandaGross(float $grossKg): string
    {
        return '[GROSS='.number_format($grossKg, 2, '.', '').']';
    }

    /**
     * Ambil nilai gross (kg) dari keterangan — null bila tidak ada.
     */
    public static function grossDariKeterangan(?string $keterangan): ?float
    {
        if ($keterangan === null || $keterangan === '') {
            return null;
        }

        if (! preg_match('/\[GROSS=([0-9.]+)\]/', $keterangan, $cocok)) {
            return null;
        }

        return (float) $cocok[1];
    }

    /**
     * Keterangan tanpa penanda GROSS (dipakai sebagai batch / catatan).
     */
    public static function batchDariKeterangan(?string $keterangan): ?string
    {
        if ($keterangan === null) {
            return null;
        }

        $bersih = trim((string) preg_replace('/\[GROSS=[0-9.]+\]/', '', $keterangan));

        return $bersih === '' ? null : $bersih;
    }
}
