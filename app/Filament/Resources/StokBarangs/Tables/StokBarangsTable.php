<?php

namespace App\Filament\Resources\StokBarangs\Tables;

use App\Filament\Pages\Laporan\LaporanKartuStok;
use App\Models\StokBarang;
use Filament\Actions\Action;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StokBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('barang_id')
            ->columns([
                TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('barang.kategori.kode')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'POY' => 'warning',
                        'DTY' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('merge')
                    ->label('Merge')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono)
                    ->copyable(),

                TextColumn::make('grade.kode')
                    ->label('Grade')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('stok_aktif')
                    ->label('Stok Aktif')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd(),

                TextColumn::make('stok_buffer')
                    ->label('Stok Buffer')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd(),

                TextColumn::make('jumlah_stok')
                    ->label('Total Stok')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd()
                    ->weight('semibold'),

                TextColumn::make('stok_pallet')
                    ->label('Pallet')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->placeholder('—')
                    ->alignEnd(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('barang')
                    ->label('Barang')
                    ->relationship('barang', 'kode_barang')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('grade')
                    ->label('Grade')
                    ->relationship('grade', 'kode')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'POY' => 'POY',
                        'DTY' => 'DTY',
                        'PENDUKUNG' => 'PENDUKUNG',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, string $kode): Builder => $q->whereHas(
                            'barang.kategori',
                            fn (Builder $k) => $k->where('kode', $kode)
                        )
                    )),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                Action::make('kartuStok')
                    ->label('Kartu Stok')
                    ->iconButton()
                    ->tooltip('Buka kartu stok')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('gray')
                    ->url(
                        fn (StokBarang $record): string => LaporanKartuStok::getUrl([
                            'barang_id' => $record->barang_id,
                            'merge' => $record->merge,
                            'grade_id' => $record->grade_id,
                        ]),
                        shouldOpenInNewTab: true,
                    ),
            ])
            ->emptyStateHeading('Belum ada posisi stok')
            ->emptyStateDescription('Posisi stok muncul setelah ada transaksi penerimaan atau penyesuaian.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
