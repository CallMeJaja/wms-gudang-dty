<?php

namespace App\Filament\Resources\PenerimaanPoy\Tables;

use App\Enums\StatusDokumen;
use App\Filament\Resources\PenerimaanPoy\PenerimaanPoyResource;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenerimaanPoyTable
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
                    ->sortable(),

                TextColumn::make('partner.nama')
                    ->label('Supplier')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('ref_dokumen')
                    ->label('Ref. Dokumen')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        StatusDokumen::Issued => 'info',
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('pembuat.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PenerimaanPoyResource::opsiStatus()),

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
            ->recordActions(PenerimaanPoyResource::aksiPenerimaan())
            ->emptyStateHeading('Belum ada penerimaan POY')
            ->emptyStateDescription('Buat transaksi penerimaan POY dari IPCI untuk menambah stok bahan baku.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
