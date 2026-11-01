<?php

namespace App\Filament\Resources\PengeluaranPoy\Tables;

use App\Enums\StatusDokumen;
use App\Filament\Resources\PengeluaranPoy\PengeluaranPoyResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengeluaranPoyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withSum('detail as total_netto', 'berat_netto_kg')
                ->withCount('detail'))
            ->columns([
                TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('shift')
                    ->label('Shift')
                    ->alignCenter(),

                TextColumn::make('operator_nama')
                    ->label('Operator')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('total_netto')
                    ->label('Total Netto')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' kg')
                    ->alignEnd(),

                TextColumn::make('detail_count')
                    ->label('Baris')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                    ->color(fn ($state) => match ($state) {
                        StatusDokumen::Draft => 'gray',
                        StatusDokumen::Posted => 'success',
                        StatusDokumen::Issued => 'info',
                        StatusDokumen::Void => 'danger',
                        StatusDokumen::Cancelled => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('pembuat.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PengeluaranPoyResource::opsiStatus()),

                PengeluaranPoyResource::filterTanggal(),
            ])
            ->recordActions(PengeluaranPoyResource::aksiPengeluaran())
            ->emptyStateHeading('Belum ada pengeluaran POY')
            ->emptyStateDescription('Buat transaksi pengeluaran POY ke produksi. Stok berkurang saat diposting.')
            ->emptyStateIcon('heroicon-o-arrow-up-tray');
    }
}
