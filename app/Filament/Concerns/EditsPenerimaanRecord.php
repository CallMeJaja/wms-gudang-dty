<?php

namespace App\Filament\Concerns;

use App\Models\ActivityLog;
use App\Models\TransaksiMasuk;
use App\Services\PenerimaanService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

/**
 * Perilaku bersama halaman "Ubah" penerimaan: aksi Posting/Issue, Void,
 * dan Hapus Draf — semuanya memakai policy & service yang sama dengan tabel.
 */
trait EditsPenerimaanRecord
{
    protected function getHeaderActions(): array
    {
        return [
            static::getResource()::aksiPosting()
                ->after(fn (Action $action) => $this->kembaliKeDaftar($action)),
            DeleteAction::make()
                ->visible(fn (): bool => static::getResource()::canDelete($this->getRecord())),
        ];
    }

    protected function afterSave(): void
    {
        /** @var TransaksiMasuk $transaksi */
        $transaksi = $this->getRecord();
        $jenis = static::getResource()::jenisMasuk();

        ActivityLog::catat(
            'UPDATE',
            PenerimaanService::modul($jenis),
            (int) $transaksi->id,
            "Ubah draf {$jenis->label()} {$transaksi->nomor_transaksi}.",
        );
    }

    /**
     * Setelah status berubah (POSTED/ISSUED/VOID), halaman edit tidak lagi
     * dapat dibuka — kembali ke daftar agar state UI tidak menyesatkan.
     */
    private function kembaliKeDaftar(Action $action): void
    {
        $livewire = $action->getLivewire();

        if ($livewire !== null) {
            $livewire->redirect(static::getResource()::getUrl());
        }
    }
}
