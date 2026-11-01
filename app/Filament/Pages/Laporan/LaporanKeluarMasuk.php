<?php

namespace App\Filament\Pages\Laporan;

use App\Models\KategoriBarang;
use App\Services\LaporanService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Laporan keluar-masuk di panel Filament.
 *
 * Filter dibaca langsung dari query string agar URL dapat dibagikan dan
 * parameter yang sama dipakai oleh endpoint export.
 */
class LaporanKeluarMasuk extends Page
{
    protected static ?string $slug = 'laporan/keluar-masuk';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Keluar-Masuk';

    protected static ?int $navigationSort = 130;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $title = 'Laporan Keluar-Masuk';

    protected string $view = 'filament.pages.laporan.keluar-masuk';

    /**
     * @return array{dari: string, sampai: string, kategori: ?string, arus: ?string, merge: ?string}
     */
    public function filter(): array
    {
        $default = LaporanService::defaultPeriode();
        $arus = request()->query('arus');

        return [
            'dari' => (string) request()->query('dari', $default['dari']),
            'sampai' => (string) request()->query('sampai', $default['sampai']),
            'kategori' => request()->query('kategori') ?: null,
            'arus' => in_array($arus, ['MASUK', 'KELUAR', 'VOID'], true) ? $arus : null,
            'merge' => trim((string) request()->query('merge', '')) ?: null,
        ];
    }

    /**
     * Hanya parameter yang benar-benar dipakai, agar URL export bersih.
     *
     * @return array<string, string>
     */
    public function exportFilter(): array
    {
        return array_filter(
            $this->filter(),
            fn ($nilai): bool => $nilai !== null && $nilai !== '',
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')
                ->label('Excel')
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->url(fn (): string => route('admin.laporan.keluar-masuk.excel', $this->exportFilter()))
                ->openUrlInNewTab(),
            Action::make('pdf')
                ->label('PDF')
                ->color('danger')
                ->icon(Heroicon::OutlinedDocument)
                ->url(fn (): string => route('admin.laporan.keluar-masuk.pdf', $this->exportFilter()))
                ->openUrlInNewTab(),
            Action::make('cetak')
                ->label('Cetak')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn (): string => route('admin.laporan.keluar-masuk.pdf', $this->exportFilter()))
                ->openUrlInNewTab(),
        ];
    }

    protected function getViewData(): array
    {
        $laporan = app(LaporanService::class);
        $filter = $this->filter();
        $baris = $laporan->barisKeluarMasuk($filter);

        return [
            'filter' => $filter,
            'baris' => $baris,
            'total' => $laporan->totalKeluarMasuk($baris),
            'daftarKategori' => KategoriBarang::query()->orderBy('kode')->get(),
        ];
    }
}
