<?php

namespace App\Filament\Pages\Laporan;

use App\Services\LaporanService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Rekonsiliasi POY vs DTY di panel Filament.
 */
class LaporanRendemen extends Page
{
    protected static ?string $slug = 'laporan/rendemen';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Rendemen';

    protected static ?int $navigationSort = 132;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $title = 'Rendemen POY vs DTY';

    protected string $view = 'filament.pages.laporan.rendemen';

    /**
     * @return array{dari: string, sampai: string}
     */
    public function filter(): array
    {
        $default = LaporanService::defaultPeriode();

        return [
            'dari' => (string) request()->query('dari', $default['dari']),
            'sampai' => (string) request()->query('sampai', $default['sampai']),
        ];
    }

    /**
     * @return array{dari: string, sampai: string}
     */
    public function exportFilter(): array
    {
        return $this->filter();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')
                ->label('Excel')
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->url(fn (): string => route('admin.laporan.rendemen.excel', $this->exportFilter()))
                ->openUrlInNewTab(),
            Action::make('pdf')
                ->label('PDF')
                ->color('danger')
                ->icon(Heroicon::OutlinedDocument)
                ->url(fn (): string => route('admin.laporan.rendemen.pdf', $this->exportFilter()))
                ->openUrlInNewTab(),
            Action::make('cetak')
                ->label('Cetak')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn (): string => route('admin.laporan.rendemen.pdf', $this->exportFilter()))
                ->openUrlInNewTab(),
        ];
    }

    protected function getViewData(): array
    {
        $filter = $this->filter();

        return [
            'filter' => $filter,
            'hasil' => app(LaporanService::class)->rendemen($filter['dari'], $filter['sampai']),
            'disclaimerRingkas' => (string) config('wms.rendemen.disclaimer_ringkas'),
            'disclaimerLengkap' => (string) config('wms.rendemen.disclaimer_lengkap'),
        ];
    }
}
