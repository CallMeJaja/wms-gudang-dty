<?php

namespace App\Filament\Resources\WasteLogs\Schemas;

use App\Models\WasteLog;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WasteLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Select::make('jenis')
                    ->label('Jenis')
                    ->options(WasteLog::JENIS_LABEL)
                    ->required()
                    ->native(false),

                Select::make('tujuan')
                    ->label('Tujuan')
                    ->options(WasteLog::TUJUAN_OPTIONS)
                    ->native(false)
                    ->placeholder('Pilih tujuan'),

                TextInput::make('berat_kg')
                    ->label('Berat (kg)')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step('0.01')
                    ->suffix('kg'),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder('Opsional')
                    ->columnSpanFull(),
            ]);
    }
}
