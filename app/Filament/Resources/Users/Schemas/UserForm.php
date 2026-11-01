<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Identitas Pengguna')
                                    ->description('Akun untuk aplikasi lama dan panel administrasi gudang.')
                                    ->icon(Heroicon::OutlinedUserCircle)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->required()
                                            ->maxLength(100),

                                        TextInput::make('username')
                                            ->label('Username')
                                            ->required()
                                            ->maxLength(50)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Dipakai di aplikasi lama.'),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(150)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Dipakai untuk login panel.'),

                                        Select::make('role')
                                            ->label('Role')
                                            ->required()
                                            ->options(fn (): array => collect(Role::cases())
                                                ->mapWithKeys(fn (Role $role): array => [$role->value => $role->label()])
                                                ->all())
                                            ->enum(Role::class)
                                            ->disabled(fn (?User $record): bool => $record !== null && $record->is(Auth::user()))
                                            ->dehydrated(),
                                    ]),

                                Section::make('Keamanan Akun')
                                    ->icon(Heroicon::OutlinedShieldCheck)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('Kata Sandi')
                                            ->password()
                                            ->revealable()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->minLength(8)
                                            ->maxLength(255)
                                            ->afterStateHydrated(fn (TextInput $component) => $component->state(null))
                                            ->dehydrated(fn ($state): bool => filled($state))
                                            ->helperText(fn (string $operation): string => $operation === 'create'
                                                ? 'Minimal 8 karakter.'
                                                : 'Kosongkan untuk mempertahankan kata sandi lama.'),

                                        TextInput::make('password_confirmation')
                                            ->label('Konfirmasi Kata Sandi')
                                            ->password()
                                            ->revealable()
                                            ->requiredWith('password')
                                            ->same('password')
                                            ->dehydrated(false),
                                    ]),
                            ]),

                        Section::make('Status Akun')
                            ->description('Kontrol akses akun ke panel dan aplikasi.')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('Nonaktifkan untuk mencabut akses panel/aplikasi.')
                                    ->disabled(fn (?User $record): bool => $record !== null && $record->is(Auth::user()))
                                    ->dehydrated(),
                            ]),
                    ]),
            ]);
    }
}
