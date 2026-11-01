<?php

namespace App\Filament\Resources\Kategoris\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class KategoriForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Kategori')
                    ->description('Kategori barang menentukan perlakuan stok (POY, DTY, pendukung).')
                    ->icon(Heroicon::OutlinedFolder)
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode Kategori')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-folder')
                            ->helperText('Unik. Contoh: POY, DTY, PENDUKUNG.'),

                        TextInput::make('nama')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
