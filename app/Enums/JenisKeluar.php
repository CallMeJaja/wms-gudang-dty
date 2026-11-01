<?php

namespace App\Enums;

enum JenisKeluar: string
{
    case PoyProduksi = 'POY_PRODUKSI';
    case DtySuratJalan = 'DTY_SURAT_JALAN';
    case DtyScrapReject = 'DTY_SCRAP_REJECT';
    case AdjustmentOut = 'ADJUSTMENT_OUT';

    public function label(): string
    {
        return match ($this) {
            self::PoyProduksi => 'Pengeluaran POY ke Produksi',
            self::DtySuratJalan => 'Pengiriman DTY (Surat Jalan)',
            self::DtyScrapReject => 'Pengeluaran Scrap / Reject DTY',
            self::AdjustmentOut => 'Penyesuaian Keluar (Opname)',
        };
    }

    public function isDokumenResmi(): bool
    {
        return $this === self::DtySuratJalan;
    }

    /**
     * Pengeluaran scrap/reject DTY wajib disertai catatan.
     */
    public function requiresCatatan(): bool
    {
        return $this === self::DtyScrapReject;
    }

    /**
     * Hanya pengeluaran POY yang memotong stok AKTIF; buffer tidak tersentuh.
     */
    public function memotongStokAktif(): bool
    {
        return $this === self::PoyProduksi;
    }
}
