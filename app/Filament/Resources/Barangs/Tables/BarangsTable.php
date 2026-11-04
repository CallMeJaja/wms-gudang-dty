<?php

namespace App\Filament\Resources\Barangs\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode_barang')
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('kode_material')
                    ->label('Kode Material')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kategori.kode')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'POY' => 'warning',
                        'DTY' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('denier_filament')
                    ->label('D/F')
                    ->placeholder('—')
                    ->alignCenter(),

                TextColumn::make('satuanDasar.kode')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('stok_minimum')
                    ->label('Stok Min.')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('—')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('is_merge_minggu')
                    ->label('Buffer Minggu')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Buffer' : '—')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->alignCenter()
                    ->tooltip('Penanda buffer Minggu (khusus POY)'),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya aktif')
                    ->falseLabel('Hanya nonaktif'),

                TernaryFilter::make('is_merge_minggu')
                    ->label('Merge Minggu')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya merge')
                    ->falseLabel('Bukan merge'),

                TrashedFilter::make()
                    ->label('Data terhapus'),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tombol edit/delete disembunyikan oleh BarangPolicy
                    // (hanya Admin yang boleh mengubah atau menghapus).
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ])->visible(fn () => Auth::user()?->canManage() ?? false),
            ])
            ->emptyStateHeading('Belum ada barang')
            ->emptyStateDescription('Tambahkan barang POY, DTY, atau pendukung untuk mulai mencatat stok.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
