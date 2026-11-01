<?php

namespace App\Filament\Concerns;

use App\Enums\StatusDokumen;
use App\Models\ActivityLog;
use App\Models\TransaksiMasuk;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Perilaku bersama halaman "Buat" penerimaan.
 *
 * Nomor transaksi selalu digenerate di server (atomik) dan status awal DRAFT
 * — mengikuti alur backend. Stok baru berubah saat Posting/Issue.
 */
trait CreatesPenerimaanRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nomor_transaksi'] = app(NomorDokumenService::class)->generate('TRX_MASUK');
        $data['jenis_masuk'] = static::getResource()::jenisMasuk();
        $data['status'] = StatusDokumen::Draft;
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function afterCreate(): void
    {
        /** @var TransaksiMasuk $transaksi */
        $transaksi = $this->getRecord();
        $jenis = static::getResource()::jenisMasuk();

        ActivityLog::catat(
            'CREATE',
            PenerimaanService::modul($jenis),
            (int) $transaksi->id,
            "Buat draf {$jenis->label()} {$transaksi->nomor_transaksi} ({$transaksi->detail()->count()} baris).",
        );

        Notification::make()
            ->title('Draf berhasil disimpan')
            ->body('Stok belum berubah — tekan Posting atau Terbitkan untuk memproses.')
            ->success()
            ->send();
    }
}
