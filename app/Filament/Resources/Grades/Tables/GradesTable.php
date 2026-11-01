<?php

namespace App\Filament\Resources\Grades\Tables;

use App\Filament\Resources\Grades\GradeResource;
use App\Models\ActivityLog;
use App\Models\Grade;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kategori_id')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('stokBarang'))
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('kategori.kode')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'POY' => 'warning',
                        'DTY' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->searchable()
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('stok_barang_count')
                    ->label('Stok')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya aktif')
                    ->falseLabel('Hanya nonaktif'),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tombol edit/delete otomatis disembunyikan oleh Policy.
                    EditAction::make(),
                    DeleteAction::make()
                        ->hidden(fn (Grade $record): bool => GradeResource::dilindungi($record))
                        ->before(function (DeleteAction $action, Grade $record): void {
                            if ($alasan = GradeResource::alasanTidakBisaDihapus($record)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Grade tidak dapat dihapus')
                                    ->body($alasan)
                                    ->send();

                                $action->halt();
                            }

                            ActivityLog::catat(
                                'DELETE',
                                'MASTER_GRADE',
                                (int) $record->id,
                                "Hapus grade: {$record->kode}",
                            );
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->iconButton(),
            ])
            ->emptyStateHeading('Belum ada grade')
            ->emptyStateDescription('Tambahkan grade untuk setiap kategori barang.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
