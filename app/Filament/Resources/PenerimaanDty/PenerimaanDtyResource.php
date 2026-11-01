<?php

namespace App\Filament\Resources\PenerimaanDty;

use App\Enums\JenisMasuk;
use App\Filament\Concerns\InteractsWithPenerimaan;
use App\Filament\Resources\PenerimaanDty\Pages\CreatePenerimaanDty;
use App\Filament\Resources\PenerimaanDty\Pages\EditPenerimaanDty;
use App\Filament\Resources\PenerimaanDty\Pages\ListPenerimaanDty;
use App\Filament\Resources\PenerimaanDty\Schemas\PenerimaanDtyForm;
use App\Filament\Resources\PenerimaanDty\Tables\PenerimaanDtyTable;
use App\Models\TransaksiMasuk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PenerimaanDtyResource extends Resource
{
    use InteractsWithPenerimaan;

    protected static ?string $model = TransaksiMasuk::class;

    protected static ?string $slug = 'penerimaan-dty';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Penerimaan DTY';

    protected static ?string $modelLabel = 'Penerimaan DTY';

    protected static ?string $pluralModelLabel = 'Penerimaan DTY';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi Masuk';

    protected static ?int $navigationSort = 102;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    public static function jenisMasuk(): JenisMasuk
    {
        return JenisMasuk::DtyProduksi;
    }

    public static function form(Schema $schema): Schema
    {
        return PenerimaanDtyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenerimaanDtyTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPenerimaanDty::route('/'),
            'create' => CreatePenerimaanDty::route('/create'),
            'edit' => EditPenerimaanDty::route('/{record}/edit'),
        ];
    }
}
