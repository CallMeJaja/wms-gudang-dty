<?php

namespace App\Filament\Resources\Partners\Tables;

use App\Models\ActivityLog;
use App\Models\SupplierCustomer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode')
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

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'SUPPLIER' => 'info',
                        'CUSTOMER' => 'success',
                        'BOTH' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('kontak')
                    ->label('Kontak')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options(array_combine(SupplierCustomer::JENIS, SupplierCustomer::JENIS)),

                TrashedFilter::make()
                    ->label('Data terhapus'),
            ])
            ->recordActions([
                // Tombol edit/delete otomatis disembunyikan oleh Policy.
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, SupplierCustomer $record): void {
                        ActivityLog::catat(
                            'DELETE',
                            'MASTER_PARTNER',
                            (int) $record->id,
                            "Hapus partner (soft delete): {$record->kode}",
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ])->visible(fn (): bool => Auth::user()?->canManage() ?? false),
            ])
            ->emptyStateHeading('Belum ada partner')
            ->emptyStateDescription('Tambahkan supplier atau customer untuk dipakai pada transaksi.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
