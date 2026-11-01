<?php

namespace App\Filament\Resources\KonversiSatuans\Tables;

use App\Filament\Resources\KonversiSatuans\KonversiSatuanResource;
use App\Models\ActivityLog;
use App\Models\KonversiSatuan;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KonversiSatuansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('barang_id')
            ->columns([
                TextColumn::make('barang.kode_barang')
                    ->label('Barang')
                    ->placeholder('Global')
                    ->searchable(['kode_barang', 'nama_barang'])
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->placeholder('Semua barang')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('dariSatuan.kode')
                    ->label('Dari')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('keSatuan.kode')
                    ->label('Ke')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('faktor')
                    ->label('Faktor')
                    ->numeric(decimalPlaces: 4, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('is_default')
                    ->label('Default')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Default' : '—')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('barang_id')
                    ->label('Barang')
                    ->relationship('barang', 'kode_barang')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('dari_satuan_id')
                    ->label('Satuan Asal')
                    ->relationship('dariSatuan', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('ke_satuan_id')
                    ->label('Satuan Tujuan')
                    ->relationship('keSatuan', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tombol edit/delete otomatis disembunyikan oleh Policy.
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, KonversiSatuan $record): void {
                            $record->load(['barang', 'dariSatuan', 'keSatuan']);

                            ActivityLog::catat(
                                'DELETE',
                                'MASTER_KONVERSI',
                                (int) $record->id,
                                'Hapus konversi: '.KonversiSatuanResource::ringkas($record),
                            );
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton(),
            ])
            ->emptyStateHeading('Belum ada konversi satuan')
            ->emptyStateDescription('Tambahkan aturan konversi untuk barang DTY atau pendukung.')
            ->emptyStateIcon('heroicon-o-arrows-right-left');
    }
}
