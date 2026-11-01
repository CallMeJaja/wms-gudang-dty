<?php

namespace App\Filament\Resources\Partners\Schemas;

use App\Models\SupplierCustomer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PartnerForm
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
                                Section::make('Identitas Mitra')
                                    ->description('Pemasok, pelanggan, atau mitra internal yang dipakai pada transaksi.')
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('kode')
                                            ->label('Kode Mitra')
                                            ->required()
                                            ->maxLength(30)
                                            ->unique(ignoreRecord: true)
                                            ->prefixIcon('heroicon-o-identification')
                                            ->helperText('Unik. Contoh: IPCI, CUST-001.'),

                                        TextInput::make('nama')
                                            ->label('Nama Mitra')
                                            ->required()
                                            ->maxLength(150),

                                        TextInput::make('kontak')
                                            ->label('Kontak')
                                            ->maxLength(100)
                                            ->placeholder('Telepon / PIC'),

                                        TextInput::make('alamat')
                                            ->label('Alamat')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Section::make('Klasifikasi')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('jenis')
                                    ->label('Jenis')
                                    ->options(fn (): array => collect(SupplierCustomer::JENIS)
                                        ->mapWithKeys(fn (string $jenis): array => [$jenis => SupplierCustomer::labelJenis($jenis)])
                                        ->all())
                                    ->required()
                                    ->native(false)
                                    ->helperText('Menentukan peran mitra pada transaksi masuk dan keluar.'),
                            ]),
                    ]),
            ]);
    }
}
