<?php

namespace App\Filament\Resources\Satuans\Tables;

use App\Filament\Resources\Satuans\SatuanResource;
use App\Models\ActivityLog;
use App\Models\Satuan;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
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

                TextColumn::make('barang_count')
                    ->label('Dipakai Barang')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            ->emptyStateHeading('Belum ada satuan')
            ->emptyStateDescription('Tambahkan satuan sebelum membuat master barang.')
            ->emptyStateIcon('heroicon-o-scale');
    }
}
