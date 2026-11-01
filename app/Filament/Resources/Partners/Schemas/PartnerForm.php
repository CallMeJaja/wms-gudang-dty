<?php

namespace App\Filament\Resources\Partners\Schemas;

use App\Models\SupplierCustomer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Partner')
                    ->description('Supplier, customer, atau partner internal yang dipakai pada transaksi.')
                    ->columns(2)
                    ->columnSpanFull()
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

                        Select::make('jenis')
                            ->label('Jenis')
                            ->options(array_combine(SupplierCustomer::JENIS, SupplierCustomer::JENIS))
                            ->required()
                            ->native(false),

                        TextInput::make('kontak')
                            ->label('Kontak')
                            ->maxLength(100)
                            ->placeholder('Telepon / PIC'),

                        TextInput::make('alamat')
                            ->label('Alamat')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
