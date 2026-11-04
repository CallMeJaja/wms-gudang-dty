<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::roleValue($state)?->label() ?? '-')
                    ->color(fn ($state): string => match (self::roleValue($state)) {
                        Role::Admin => 'danger',
                        Role::Operator => 'info',
                        Role::Manajer => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->extraCellAttributes(['class' => 'wms-nowrap'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->options(fn (): array => collect(Role::cases())
                        ->mapWithKeys(fn (Role $role): array => [$role->value => $role->label()])
                        ->all()),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->placeholder('Semua status'),
            ])
            ->actionsColumnLabel('Aksi')
            ->recordActions([
                EditAction::make(),
            ])
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Tambahkan pengguna untuk mengakses panel ini.')
            ->emptyStateIcon('heroicon-o-users');
    }

    private static function roleValue(mixed $state): ?Role
    {
        if ($state instanceof Role) {
            return $state;
        }

        return is_string($state) ? Role::tryFrom($state) : null;
    }
}
