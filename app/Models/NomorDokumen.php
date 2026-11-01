<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorDokumen extends Model
{
    protected $table = 'nomor_dokumen';

    protected $fillable = ['jenis', 'periode', 'last_number'];

    protected function casts(): array
    {
        return [
            'last_number' => 'integer',
        ];
    }

    public const SURAT_JALAN_DTY = 'SURAT_JALAN_DTY';

    public const GATE_PASS = 'GATE_PASS';

    public const TRX_MASUK = 'TRX_MASUK';

    public const TRX_KELUAR = 'TRX_KELUAR';

    /**
     * Periode berjalan untuk jenis dokumen tertentu, mengikuti
     * config('wms.periode_nomor'). Bulanan -> 'YYYY-MM', harian -> 'YYYY-MM-DD'.
     */
    public static function periodeUntuk(string $jenis, ?\DateTimeInterface $saat = null): string
    {
        $saat ??= now();
        $mode = config("wms.periode_nomor.{$jenis}", 'bulanan');

        return $mode === 'harian'
            ? $saat->format('Y-m-d')
            : $saat->format('Y-m');
    }
}
