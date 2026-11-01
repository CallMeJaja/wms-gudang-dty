<?php

namespace App\Enums;

/**
 * Status terpadu untuk transaksi internal dan dokumen resmi.
 *
 * Dua lifecycle berbagi satu enum:
 *  - Internal gudang : DRAFT -> POSTED -> VOID
 *  - Dokumen resmi   : DRAFT -> ISSUED -> CANCELLED (atau VOID)
 */
enum StatusDokumen: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Issued = 'ISSUED';
    case Void = 'VOID';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Posted => 'Diposting',
            self::Issued => 'Diterbitkan',
            self::Void => 'Dibatalkan (Void)',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Status final tidak dapat diubah lagi.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::Void, self::Cancelled], true);
    }

    /**
     * Apakah transaksi pada status ini sudah mempengaruhi saldo stok.
     */
    public function affectsStock(): bool
    {
        return in_array($this, [self::Posted, self::Issued], true);
    }

    /**
     * Status tujuan yang diizinkan dari status ini.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Posted, self::Issued, self::Cancelled, self::Void],
            self::Posted => [self::Void],
            self::Issued => [self::Cancelled, self::Void],
            self::Void, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
