<?php

namespace App\Filament\Resources\PengeluaranScrap\Tables;

use App\Enums\StatusDokumen;
use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengeluaranScrapTable
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

                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->placeholder('—')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('total_netto')
                    ->label('Total Netto')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' kg')
                    ->alignEnd(),

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
                    ->options(PengeluaranScrapResource::opsiStatus()),

                PengeluaranScrapResource::filterTanggal(),
            ])
            ->recordActions(PengeluaranScrapResource::aksiPengeluaran())
            ->emptyStateHeading('Belum ada pengeluaran scrap')
            ->emptyStateDescription('Catat scrap/reject DTY beserta tujuan atau penerimanya.')
            ->emptyStateIcon('heroicon-o-trash');
    }
}
