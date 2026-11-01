<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Sistem')
                    ->toggleable(),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOGIN', 'RELEASE_BUFFER' => 'success',
                        'LOGOUT' => 'gray',
                        'CREATE' => 'info',
                        'UPDATE' => 'warning',
                        'DELETE', 'VOID' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('modul')
                    ->label('Modul')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('ref_id')
                    ->label('Ref ID')
                    ->fontFamily(FontFamily::Mono)
                    ->toggleable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(80)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->wrap(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily(FontFamily::Mono)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(60)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'LOGIN' => 'LOGIN',
                        'LOGOUT' => 'LOGOUT',
                        'CREATE' => 'CREATE',
                        'UPDATE' => 'UPDATE',
                        'DELETE' => 'DELETE',
                        'VOID' => 'VOID',
                        'RELEASE_BUFFER' => 'RELEASE_BUFFER',
                    ]),

                SelectFilter::make('modul')
                    ->label('Modul')
                    ->options(fn (): array => ActivityLog::query()
                        ->distinct()
                        ->orderBy('modul')
                        ->pluck('modul', 'modul')
                        ->all()),

                SelectFilter::make('user')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('dari')->label('Dari')->native(false),
                        DatePicker::make('sampai')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $d): Builder => $q->whereDate('created_at', '>=', $d))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $d): Builder => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([])
            ->emptyStateHeading('Belum ada activity log')
            ->emptyStateDescription('Aktivitas pengguna akan tercatat otomatis di sini.')
            ->emptyStateIcon('heroicon-o-clock');
    }
}
