<?php

namespace App\Filament\Resources\PenerimaanDty\Tables;

use App\Enums\StatusDokumen;
use App\Filament\Resources\PenerimaanDty\PenerimaanDtyResource;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenerimaanDtyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withSum('detail as total_kg', 'berat_kg')
                ->withCount('detail'))
            ->columns([
                TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->placeholder('(draf)')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable(),

                TextColumn::make('merge_poy_asal')
                    ->label('Merge POY Asal')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('sumber_internal')
                    ->label('Sumber')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('total_kg')
                    ->label('Total KG')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
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
                        StatusDokumen::Void => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('shift')
                    ->label('Shift')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('operator_nama')
                    ->label('Operator')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('pembuat.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PenerimaanDtyResource::opsiStatus()),

                Filter::make('tanggal')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('dari')->label('Dari')->native(false),
                        DatePicker::make('sampai')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '>=', $d))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '<=', $d))),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions(PenerimaanDtyResource::aksiPenerimaan())
            ->emptyStateHeading('Belum ada penerimaan DTY')
            ->emptyStateDescription('Catat hasil produksi DTY agar stok produk jadi bertambah.')
            ->emptyStateIcon('heroicon-o-arrow-down-tray');
    }
}
