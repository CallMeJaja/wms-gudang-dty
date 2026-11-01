<?php

namespace App\Filament\Resources\PengeluaranPoy;

use App\Enums\JenisKeluar;
use App\Filament\Concerns\InteractsWithPengeluaran;
use App\Filament\Resources\PengeluaranPoy\Pages\CreatePengeluaranPoy;
use App\Filament\Resources\PengeluaranPoy\Pages\EditPengeluaranPoy;
use App\Filament\Resources\PengeluaranPoy\Pages\ListPengeluaranPoy;
use App\Filament\Resources\PengeluaranPoy\Schemas\PengeluaranPoyForm;
use App\Filament\Resources\PengeluaranPoy\Tables\PengeluaranPoyTable;
use App\Models\TransaksiKeluar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PengeluaranPoyResource extends Resource
{
    use InteractsWithPengeluaran;

    protected static ?string $model = TransaksiKeluar::class;

    protected static ?string $slug = 'pengeluaran-poy';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'POY';

    protected static ?string $modelLabel = 'Pengeluaran POY';

    protected static ?string $pluralModelLabel = 'Pengeluaran POY';

    protected static string|UnitEnum|null $navigationGroup = 'Pengeluaran';

    protected static ?int $navigationSort = 110;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    public static function jenisKeluar(): JenisKeluar
    {
        return JenisKeluar::PoyProduksi;
    }

    public static function form(Schema $schema): Schema
    {
        return PengeluaranPoyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengeluaranPoyTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengeluaranPoy::route('/'),
            'create' => CreatePengeluaranPoy::route('/create'),
            'edit' => EditPengeluaranPoy::route('/{record}/edit'),
        ];
    }
}
