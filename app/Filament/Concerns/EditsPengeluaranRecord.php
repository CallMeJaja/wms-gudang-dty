<?php

namespace App\Filament\Concerns;

use App\Models\ActivityLog;
use App\Models\TransaksiKeluar;
use App\Services\PengeluaranService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

/**
 * Perilaku bersama halaman "Ubah" pengeluaran: aksi Posting/Issue dan Hapus
 * Draf — semuanya memakai policy & service yang sama dengan tabel.
 *
 * Void tidak ditampilkan di sini; void hanya untuk record POSTED/ISSUED dan
 * tersedia sebagai aksi tabel.
 */
trait EditsPengeluaranRecord
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
        /** @var TransaksiKeluar $transaksi */
        $transaksi = $this->getRecord();
        $jenis = static::getResource()::jenisKeluar();

        ActivityLog::catat(
            'UPDATE',
            PengeluaranService::modul($jenis),
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
