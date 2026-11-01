<?php

namespace App\Filament\Resources\Satuans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SatuanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Satuan')
                    ->description('Satuan dasar yang dipakai pada master barang dan transaksi.')
                    ->icon(Heroicon::OutlinedScale)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode Satuan')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-scale')
                            ->helperText('Unik. Contoh: KG, PCS, BOBBIN.'),

                        TextInput::make('nama')
                            ->label('Nama Satuan')
                            ->required()
                            ->maxLength(100),
                    ]),
            ]);
    }
}
