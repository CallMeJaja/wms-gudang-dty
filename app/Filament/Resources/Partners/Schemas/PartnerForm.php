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
                        Section::make('Identitas Partner')
                            ->description('Supplier, customer, atau partner internal yang dipakai pada transaksi.')
                            ->icon(Heroicon::OutlinedIdentification)
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('kode')
                                    ->label('Kode Partner')
                                    ->required()
                                    ->maxLength(30)
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-o-identification')
                                    ->helperText('Unik. Contoh: IPCI, CUST-001.'),

                                TextInput::make('nama')
                                    ->label('Nama Partner')
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

                        Section::make('Klasifikasi')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('jenis')
                                    ->label('Jenis')
                                    ->options(array_combine(SupplierCustomer::JENIS, SupplierCustomer::JENIS))
                                    ->required()
                                    ->native(false)
                                    ->helperText('Menentukan peran partner pada transaksi masuk dan keluar.'),
                            ]),
                    ]),
            ]);
    }
}
