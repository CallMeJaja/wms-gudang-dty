<?php

namespace App\Filament\Resources\PenerimaanPoy;

use App\Enums\JenisMasuk;
use App\Filament\Concerns\InteractsWithPenerimaan;
use App\Filament\Resources\PenerimaanPoy\Pages\CreatePenerimaanPoy;
use App\Filament\Resources\PenerimaanPoy\Pages\EditPenerimaanPoy;
use App\Filament\Resources\PenerimaanPoy\Pages\ListPenerimaanPoy;
use App\Filament\Resources\PenerimaanPoy\Schemas\PenerimaanPoyForm;
use App\Filament\Resources\PenerimaanPoy\Tables\PenerimaanPoyTable;
use App\Models\TransaksiMasuk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PenerimaanPoyResource extends Resource
{
    use InteractsWithPenerimaan;

    protected static ?string $model = TransaksiMasuk::class;

    protected static ?string $slug = 'penerimaan-poy';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'POY';

    protected static ?string $modelLabel = 'Penerimaan POY';

    protected static ?string $pluralModelLabel = 'Penerimaan POY';

    protected static string|UnitEnum|null $navigationGroup = 'Penerimaan';

    protected static ?int $navigationSort = 100;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    public static function jenisMasuk(): JenisMasuk
    {
        return JenisMasuk::PoyIpci;
    }

    public static function form(Schema $schema): Schema
    {
        return PenerimaanPoyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenerimaanPoyTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPenerimaanPoy::route('/'),
            'create' => CreatePenerimaanPoy::route('/create'),
            'edit' => EditPenerimaanPoy::route('/{record}/edit'),
        ];
    }
}
