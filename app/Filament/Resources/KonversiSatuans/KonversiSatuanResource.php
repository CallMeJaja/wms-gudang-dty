<?php

namespace App\Filament\Resources\KonversiSatuans;

use App\Filament\Resources\KonversiSatuans\Pages\CreateKonversiSatuan;
use App\Filament\Resources\KonversiSatuans\Pages\EditKonversiSatuan;
use App\Filament\Resources\KonversiSatuans\Pages\ListKonversiSatuans;
use App\Filament\Resources\KonversiSatuans\Schemas\KonversiSatuanForm;
use App\Filament\Resources\KonversiSatuans\Tables\KonversiSatuansTable;
use App\Models\KonversiSatuan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KonversiSatuanResource extends Resource
{
    protected static ?string $model = KonversiSatuan::class;

    protected static ?string $slug = 'konversi-satuans';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Konversi';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 15;

    protected static ?string $modelLabel = 'Konversi Satuan';

    protected static ?string $pluralModelLabel = 'Konversi Satuan';

    protected static ?string $recordTitleAttribute = 'id';

    public static function ringkas(KonversiSatuan $konversi): string
    {
        $barang = $konversi->barang?->kode_barang ?? 'Global';
        $dari = $konversi->dariSatuan?->kode ?? '?';
        $ke = $konversi->keSatuan?->kode ?? '?';

        return "{$barang}: 1 {$dari} = {$konversi->faktor} {$ke}";
    }

    public static function form(Schema $schema): Schema
    {
        return KonversiSatuanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KonversiSatuansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKonversiSatuans::route('/'),
            'create' => CreateKonversiSatuan::route('/create'),
            'edit' => EditKonversiSatuan::route('/{record}/edit'),
        ];
    }
}
