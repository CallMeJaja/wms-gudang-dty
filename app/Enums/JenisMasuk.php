<?php

namespace App\Enums;

enum JenisMasuk: string
{
    case PoyIpci = 'POY_IPCI';
    case PendukungGatepass = 'PENDUKUNG_GATEPASS';
    case DtyProduksi = 'DTY_PRODUKSI';
    case AdjustmentIn = 'ADJUSTMENT_IN';

    public function label(): string
    {
        return match ($this) {
            self::PoyIpci => 'Penerimaan POY dari IPCI',
            self::PendukungGatepass => 'Penerimaan Item Pendukung (Gate Pass)',
            self::DtyProduksi => 'Penerimaan DTY dari Produksi',
            self::AdjustmentIn => 'Penyesuaian Masuk (Opname)',
        };
    }

    /**
     * Apakah jenis ini memakai lifecycle dokumen resmi (DRAFT->ISSUED->CANCELLED).
     */
    public function isDokumenResmi(): bool
    {
        return $this === self::PendukungGatepass;
    }
}
