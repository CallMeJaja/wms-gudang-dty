<?php

namespace App\Filament\Pages\Laporan;

use App\Models\Barang;
use App\Models\Grade;
use App\Services\LaporanService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Kartu stok di panel Filament.
 *
 * Perhitungan saldo awal & saldo berjalan tetap berasal dari LaporanService;
 * halaman ini hanya menyajikan filter dan tabel.
 */
class LaporanKartuStok extends Page
{
    protected static ?string $slug = 'laporan/kartu-stok';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Kartu Stok';

    protected static ?int $navigationSort = 131;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $title = 'Kartu Stok';

    protected string $view = 'filament.pages.laporan.kartu-stok';

    /**
     * @return array{dari: string, sampai: string, barang_id: ?int, merge: string, grade_id: ?int, pos: string}
     */
    public function filter(): array
    {
        $default = LaporanService::defaultPeriode();
        $pos = (string) request()->query('pos', '');

        return [
            'dari' => (string) request()->query('dari', $default['dari']),
            'sampai' => (string) request()->query('sampai', $default['sampai']),
            'barang_id' => filled(request()->query('barang_id')) ? (int) request()->query('barang_id') : null,
            'merge' => trim((string) request()->query('merge', '')),
            'grade_id' => filled(request()->query('grade_id')) ? (int) request()->query('grade_id') : null,
            'pos' => in_array($pos, ['AKTIF', 'BUFFER'], true) ? $pos : '',
        ];
    }

    public function lengkap(): bool
    {
        $filter = $this->filter();

        return $filter['barang_id'] !== null && $filter['merge'] !== '';
    }

    /**
     * Hanya sertakan kunci yang terisi agar endpoint export tetap valid.
     *
     * @return array<string, int|string>
     */
    public function exportFilter(): array
    {
        $filter = $this->filter();

        $hasil = [
            'dari' => $filter['dari'],
            'sampai' => $filter['sampai'],
        ];

        if ($filter['barang_id'] !== null) {
            $hasil['barang_id'] = $filter['barang_id'];
        }

        if ($filter['merge'] !== '') {
            $hasil['merge'] = $filter['merge'];
        }

        if ($filter['grade_id'] !== null) {
            $hasil['grade_id'] = $filter['grade_id'];
        }

        if ($filter['pos'] !== '') {
            $hasil['pos'] = $filter['pos'];
        }

        return $hasil;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')
                ->label('Excel')
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->url(fn (): string => route('admin.laporan.kartu-stok.excel', $this->exportFilter()))
                ->openUrlInNewTab(),
            Action::make('pdf')
                ->label('PDF')
                ->color('danger')
                ->icon(Heroicon::OutlinedDocument)
                ->url(fn (): string => route('admin.laporan.kartu-stok.pdf', $this->exportFilter()))
                ->openUrlInNewTab(),
            Action::make('cetak')
                ->label('Cetak')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn (): string => route('admin.laporan.kartu-stok.pdf', $this->exportFilter()))
                ->openUrlInNewTab(),
        ];
    }

    protected function getViewData(): array
    {
        $filter = $this->filter();
        $lengkap = $this->lengkap();

        $hasil = $lengkap
            ? app(LaporanService::class)->kartuStok(
                (int) $filter['barang_id'],
                $filter['merge'],
                $filter['grade_id'],
                $filter['pos'] ?: null,
                $filter['dari'],
                $filter['sampai'],
            )
            : ['saldoAwal' => 0.0, 'baris' => collect()];

        return [
            'filter' => $filter,
            'lengkap' => $lengkap,
            'saldoAwal' => $hasil['saldoAwal'],
            'baris' => $hasil['baris'],
            'barang' => $lengkap ? Barang::query()->find($filter['barang_id']) : null,
            'daftarBarang' => Barang::aktif()->with('kategori')->orderBy('kode_barang')->get(),
            'daftarGrade' => Grade::aktif()->orderBy('kode')->get(),
        ];
    }
}
