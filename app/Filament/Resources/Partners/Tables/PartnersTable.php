<?php

namespace App\Filament\Resources\Partners\Tables;

use App\Models\ActivityLog;
use App\Models\SupplierCustomer;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
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
                    ->formatStateUsing(fn (?string $state): string => SupplierCustomer::labelJenis($state))
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
                    ->options(fn (): array => collect(SupplierCustomer::JENIS)
                        ->mapWithKeys(fn (string $jenis): array => [$jenis => SupplierCustomer::labelJenis($jenis)])
                        ->all()),

                TrashedFilter::make()
                    ->label('Data terhapus'),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tombol edit/delete otomatis disembunyikan oleh Policy.
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, SupplierCustomer $record): void {
                            ActivityLog::catat(
                                'DELETE',
                                'MASTER_PARTNER',
                                (int) $record->id,
                                "Hapus mitra (soft delete): {$record->kode}",
                            );
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ])->visible(fn (): bool => Auth::user()?->canManage() ?? false),
            ])
            ->emptyStateHeading('Belum ada mitra')
            ->emptyStateDescription('Tambahkan supplier atau customer untuk dipakai pada transaksi.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
