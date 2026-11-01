<?php

namespace App\Filament\Resources\Kategoris\Tables;

use App\Filament\Resources\Kategoris\KategoriResource;
use App\Models\ActivityLog;
use App\Models\KategoriBarang;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KategorisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['barang', 'grade']))
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('barang_count')
                    ->label('Barang')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('grade_count')
                    ->label('Grade')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tombol edit/delete otomatis disembunyikan oleh Policy.
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, KategoriBarang $record): void {
                            if ($alasan = KategoriResource::alasanTidakBisaDihapus($record)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Kategori tidak dapat dihapus')
                                    ->body($alasan)
                                    ->send();

                                $action->halt();
                            }

                            ActivityLog::catat(
                                'DELETE',
                                'MASTER_KATEGORI',
                                (int) $record->id,
                                "Hapus kategori: {$record->kode}",
                            );
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton(),
            ])
            ->emptyStateHeading('Belum ada kategori')
            ->emptyStateDescription('Tambahkan kategori barang sebelum membuat master barang.')
            ->emptyStateIcon('heroicon-o-folder');
    }
}
