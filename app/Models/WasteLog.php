<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteLog extends Model
{
    public const JENIS_WASTE = 'WASTE';

    public const JENIS_REJECT = 'REJECT';

    public const JENIS_SCRAP_PACKAGING = 'SCRAP_PACKAGING';

    public const JENIS_LABEL = [
        self::JENIS_WASTE => 'Waste',
        self::JENIS_REJECT => 'Reject',
        self::JENIS_SCRAP_PACKAGING => 'Scrap Packaging',
    ];

    public const TUJUAN_OPTIONS = [
        'RCL' => 'RCL',
        'EYD' => 'EYD',
        'Pengepul' => 'Pengepul',
        'Lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'tanggal',
        'jenis',
        'tujuan',
        'berat_kg',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'berat_kg' => 'decimal:2',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labelJenis(): string
    {
        return self::JENIS_LABEL[$this->jenis] ?? $this->jenis;
    }
}
