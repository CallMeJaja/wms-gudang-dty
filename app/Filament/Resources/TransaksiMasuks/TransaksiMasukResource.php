<?php

namespace App\Filament\Resources\TransaksiMasuks;

use App\Filament\Resources\TransaksiMasuks\Pages\CreateTransaksiMasuk;
use App\Filament\Resources\TransaksiMasuks\Pages\EditTransaksiMasuk;
use App\Filament\Resources\TransaksiMasuks\Pages\ListTransaksiMasuks;
use App\Filament\Resources\TransaksiMasuks\Schemas\TransaksiMasukForm;
use App\Filament\Resources\TransaksiMasuks\Tables\TransaksiMasuksTable;
use App\Models\TransaksiMasuk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransaksiMasukResource extends Resource
{
    protected static ?string $model = TransaksiMasuk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Penerimaan';

    protected static ?string $modelLabel = 'Penerimaan';

    protected static ?string $pluralModelLabel = 'Penerimaan';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    public static function form(Schema $schema): Schema
    {
        return TransaksiMasukForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransaksiMasuksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransaksiMasuks::route('/'),
            'create' => CreateTransaksiMasuk::route('/create'),
            'edit' => EditTransaksiMasuk::route('/{record}/edit'),
        ];
    }
}
