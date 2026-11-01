<?php

namespace App\Filament\Resources\PenerimaanPendukung\Pages;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use App\Models\TransaksiMasuk;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;

/**
 * Halaman cetak Gate Pass Pendukung.
 *
 * Hanya dokumen yang sudah diterbitkan yang boleh dicetak, sehingga draf
 * tidak dapat dibuka lewat URL cetak (403).
 */
class CetakGatePass extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PenerimaanPendukungResource::class;

    protected string $view = 'filament.penerimaan.gate-pass-cetak';

    protected static string $layout = 'filament.pengeluaran.cetak-layout';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(Gate::allows('view', [$this->record, JenisMasuk::PendukungGatepass]), 403);

        $this->record->loadMissing([
            'detail.barang.satuanDasar',
            'detail.satuanKemasan',
            'partner',
        ]);
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record instanceof TransaksiMasuk) {
            return false;
        }

        return $record->status !== StatusDokumen::Draft
            && Gate::allows('view', [$record, JenisMasuk::PendukungGatepass]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Cetak Gate Pass';
    }

    public function getBreadcrumb(): string
    {
        return 'Cetak';
    }
}
