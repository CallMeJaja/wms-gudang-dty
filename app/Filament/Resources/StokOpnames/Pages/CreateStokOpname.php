<?php

namespace App\Filament\Resources\StokOpnames\Pages;

use App\Exceptions\AturanBisnisException;
use App\Filament\Concerns\LabelsCreateFormActions;
use App\Filament\Resources\StokOpnames\StokOpnameResource;
use App\Models\Barang;
use App\Services\StokOpnameService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateStokOpname extends CreateRecord
{
    use LabelsCreateFormActions;

    protected static string $resource = StokOpnameResource::class;

    /**
     * Semua mutasi stok dijalankan oleh StokOpnameService (satu pintu).
     * Pelanggaran aturan bisnis dikembalikan sebagai error form agar
     * pengguna dapat memperbaiki input tanpa kehilangan state.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $barang = Barang::query()->findOrFail($data['barang_id']);

        try {
            return app(StokOpnameService::class)->proses(
                $barang,
                (string) $data['merge'],
                (int) $data['grade_id'],
                (float) $data['stok_fisik_qty'],
                (string) $data['tanggal'],
                (int) Auth::id(),
                $data['keterangan'] ?? null,
            );
        } catch (AturanBisnisException $e) {
            Notification::make()
                ->title('Opname gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                $this->errorKey('stok_fisik_qty') => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kunci error mengikuti state path form Filament (biasanya `data.`)
     * agar pesan tampil langsung pada field Stok Fisik.
     */
    private function errorKey(string $field): string
    {
        $statePath = $this->form->getStatePath();

        return filled($statePath) ? "{$statePath}.{$field}" : $field;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Opname tersimpan')
            ->body('Stok disinkronkan dengan fisik.')
            ->success()
            ->send();
    }
}
