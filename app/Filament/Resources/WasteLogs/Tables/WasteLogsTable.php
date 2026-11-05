<?php

namespace App\Filament\Resources\WasteLogs\Tables;

use App\Models\WasteLog;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WasteLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable(),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WasteLog::JENIS_LABEL[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        WasteLog::JENIS_WASTE => 'warning',
                        WasteLog::JENIS_REJECT => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('berat_kg')
                    ->label('Berat (kg)')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->limit(50)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->searchable(),

                TextColumn::make('createdBy.name')
                    ->label('Dicatat oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('dari')->label('Dari')->native(false),
                        DatePicker::make('sampai')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '>=', $d))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '<=', $d))),

                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options(WasteLog::JENIS_LABEL),

                SelectFilter::make('tujuan')
                    ->label('Tujuan')
                    ->options(WasteLog::TUJUAN_OPTIONS),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Belum ada catatan waste / scrap')
            ->emptyStateDescription('Catat berat waste atau reject tanpa mengubah stok DTY.')
            ->emptyStateIcon('heroicon-o-trash');
    }
}
