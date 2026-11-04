<?php

namespace App\Filament\Resources\SuratJalanDty\Pages;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use App\Models\TransaksiKeluar;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;

/**
 * Halaman cetak Surat Jalan DTY.
 *
 * Memakai layout kosong agar Blade cetak berdiri sendiri (tanpa sidebar /
 * aset panel) dan siap dicetak langsung dari browser.
 */
class CetakSuratJalan extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SuratJalanDtyResource::class;

    protected string $view = 'filament.pengeluaran.surat-jalan-cetak';

    protected static string $layout = 'filament.pengeluaran.cetak-layout';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::canAccess(['record' => $this->getRecord()]), 403);

        $this->record->loadMissing([
            'detail.barang',
            'detail.grade',
            'detail.satuanKemasan',
            'partner',
        ]);
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record instanceof TransaksiKeluar) {
            return false;
        }

        return $record->status === StatusDokumen::Issued
            && Gate::allows('view', [$record, JenisKeluar::DtySuratJalan]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Cetak Surat Jalan';
    }

    public function getBreadcrumb(): string
    {
        return 'Cetak';
    }
}
