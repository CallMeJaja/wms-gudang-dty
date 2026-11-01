<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Log audit sistem — append-only / immutable.
 *
 * Hanya menulis created_at. Tidak ada updated_at sehingga model
 * dikonfigurasi $timestamps = false.
 */
class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    /**
     * Mematikan timestamps otomatis; created_at diisi manual.
     */
    public $timestamps = false;

    public const EVENT_LABEL = [
        'LOGIN' => 'Masuk',
        'LOGOUT' => 'Keluar',
        'CREATE' => 'Tambah',
        'UPDATE' => 'Ubah',
        'DELETE' => 'Hapus',
        'VOID' => 'Void',
        'RELEASE_BUFFER' => 'Rilis Buffer',
    ];

    public const MODUL_LABEL = [
        'MASTER_PARTNER' => 'Mitra',
        'MASTER_GRADE' => 'Grade',
        'MASTER_KATEGORI' => 'Kategori',
        'MASTER_SATUAN' => 'Satuan',
        'MASTER_KONVERSI' => 'Konversi',
        'MASTER_BARANG' => 'Barang',
        'TRANSAKSI_MASUK' => 'Transaksi Masuk',
        'TRANSAKSI_KELUAR' => 'Transaksi Keluar',
        'STOK_OPNAME' => 'Stok Opname',
        'AUTH' => 'Autentikasi',
    ];

    protected $fillable = [
        'user_id',
        'event',
        'modul',
        'ref_id',
        'keterangan',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'ref_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function labelEvent(?string $event): string
    {
        return self::EVENT_LABEL[$event] ?? Str::headline((string) $event);
    }

    public static function labelModul(?string $modul): string
    {
        return self::MODUL_LABEL[$modul] ?? Str::headline((string) $modul);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Pencatatan
    |--------------------------------------------------------------------------
    */

    /**
     * Catat satu aktivitas. user_id & ip_address diambil otomatis
     * dari request aktif bila tidak diberikan.
     */
    public static function catat(
        string $event,
        string $modul,
        ?int $refId = null,
        ?string $keterangan = null,
        array $override = []
    ): self {
        return self::create(array_merge([
            'user_id' => Auth::id(),
            'event' => $event,
            'modul' => $modul,
            'ref_id' => $refId,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'created_at' => now(),
        ], $override));
    }
}
