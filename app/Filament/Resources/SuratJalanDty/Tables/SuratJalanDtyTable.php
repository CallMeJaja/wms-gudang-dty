<?php

namespace App\Filament\Resources\SuratJalanDty\Tables;

use App\Enums\StatusDokumen;
use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuratJalanDtyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withSum('detail as total_netto', 'berat_netto_kg')
                ->withSum('detail as total_gross', 'berat_gross_kg')
                ->withCount('detail'))
            ->columns([
                TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('nomor_surat_jalan')
                    ->label('No. Surat Jalan')
                    ->placeholder('— (belum terbit)')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable(),

                TextColumn::make('partner.nama')
                    ->label('Mitra')
                    ->placeholder('—'),

                TextColumn::make('no_kendaraan')
                    ->label('Kendaraan')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nama_sopir')
                    ->label('Sopir')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_netto')
                    ->label('Total Netto')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' kg')
                    ->alignEnd(),

                TextColumn::make('total_gross')
                    ->label('Total Gross')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' kg')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->options(SuratJalanDtyResource::opsiStatus()),

                SelectFilter::make('partner')
                    ->label('Mitra')
                    ->relationship('partner', 'nama')
                    ->searchable()
                    ->preload(),

                SuratJalanDtyResource::filterTanggal(),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions(SuratJalanDtyResource::aksiPengeluaran())
            ->emptyStateHeading('Belum ada Surat Jalan DTY')
            ->emptyStateDescription('Buat Surat Jalan DTY, lalu Terbitkan untuk mengurangi stok dan mencetak dokumen.')
            ->emptyStateIcon('heroicon-o-truck');
    }
}
