<?php

namespace App\Filament\Resources\StokOpnames\Tables;

use App\Models\StokOpname;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StokOpnamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'barang.kategori',
                'grade',
                'pembuat',
                'transaksiMasuk',
                'transaksiKeluar',
            ]))
            ->columns([
                TextColumn::make('nomor_opname')
                    ->label('No. Opname')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->fontFamily(FontFamily::Mono)
                    ->limit(18)
                    ->tooltip(fn ($state): string => (string) $state)
                    ->copyable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('barang.kode_barang')
                    ->label('Barang')
                    ->searchable()
                    ->sortable()
                    ->limit(18)
                    ->tooltip(fn ($state): string => (string) $state),

                TextColumn::make('merge')
                    ->label('Merge')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono)
                    ->limit(14)
                    ->tooltip(fn ($state): string => (string) $state),

                TextColumn::make('grade.kode')
                    ->label('Grade')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('stok_sistem_qty')
                    ->label('Sistem')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd(),

                TextColumn::make('stok_fisik_qty')
                    ->label('Fisik')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd(),

                TextColumn::make('selisih_qty')
                    ->label('Selisih')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd()
                    ->weight('semibold')
                    ->color(fn ($state): string => match (true) {
                        (float) $state > 0 => 'success',
                        (float) $state < 0 => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('penyesuaian')
                    ->label('Penyesuaian')
                    ->state(fn (StokOpname $record): string => match (true) {
                        $record->transaksi_masuk_id !== null => 'IN',
                        $record->transaksi_keluar_id !== null => 'OUT',
                        default => '—',
                    })
                    ->badge()
                    ->tooltip(fn (StokOpname $record): string => self::labelPenyesuaian($record))
                    ->color(fn (StokOpname $record): string => match (true) {
                        $record->transaksi_masuk_id !== null => 'success',
                        $record->transaksi_keluar_id !== null => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('pembuat.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('dari')->label('Dari')->native(false),
                        DatePicker::make('sampai')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '>=', $d))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '<=', $d))),

                SelectFilter::make('barang')
                    ->label('Barang')
                    ->relationship('barang', 'kode_barang')
                    ->searchable()
                    ->preload(),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                static::aksiLihat(),
            ])
            ->emptyStateHeading('Belum ada stock opname')
            ->emptyStateDescription('Catat hasil hitung fisik untuk menyinkronkan stok sistem.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }

    public static function aksiLihat(): Action
    {
        return Action::make('lihat')
            ->label('Lihat')
            ->iconButton()
            ->tooltip('Lihat ringkasan opname')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (StokOpname $record): string => (string) $record->nomor_opname)
            ->modalWidth('5xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(fn (StokOpname $record) => view('filament.stok-opname.ringkasan', [
                'opname' => $record->loadMissing([
                    'barang.kategori',
                    'grade',
                    'pembuat',
                    'transaksiMasuk',
                    'transaksiKeluar',
                ]),
            ]));
    }

    private static function labelPenyesuaian(StokOpname $record): string
    {
        if ($record->transaksi_masuk_id !== null) {
            return 'IN '.($record->transaksiMasuk?->nomor_transaksi ?? '—');
        }

        if ($record->transaksi_keluar_id !== null) {
            return 'OUT '.($record->transaksiKeluar?->nomor_transaksi ?? '—');
        }

        return '—';
    }
}
