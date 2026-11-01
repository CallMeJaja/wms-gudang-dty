<?php

namespace App\Filament\Resources\Satuans\Tables;

use App\Filament\Resources\Satuans\SatuanResource;
use App\Models\ActivityLog;
use App\Models\Satuan;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SatuansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('barang'))
            ->columns([
                ColumnGroup::make('Identitas', [
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
                ]),

                ColumnGroup::make('Pemakaian', [
                    TextColumn::make('barang_count')
                        ->label('Dipakai Barang')
                        ->numeric()
                        ->alignCenter()
                        ->sortable(),
                ]),
            ])
            ->filters([
                //
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->paginationPageOptions([10, 25, 50])
            ->striped()
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tombol edit/delete otomatis disembunyikan oleh Policy.
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, Satuan $record): void {
                            if ($alasan = SatuanResource::alasanTidakBisaDihapus($record)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Satuan tidak dapat dihapus')
                                    ->body($alasan)
                                    ->send();

                                $action->halt();
                            }

                            ActivityLog::catat(
                                'DELETE',
                                'MASTER_SATUAN',
                                (int) $record->id,
                                "Hapus satuan: {$record->kode}",
                            );
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton(),
            ])
            ->emptyStateHeading('Belum ada satuan')
            ->emptyStateDescription('Tambahkan satuan sebelum membuat master barang.')
            ->emptyStateIcon('heroicon-o-scale');
    }
}
