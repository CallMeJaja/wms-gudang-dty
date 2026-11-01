<?php

namespace App\Filament\Resources\Kategoris;

use App\Filament\Resources\Kategoris\Pages\CreateKategori;
use App\Filament\Resources\Kategoris\Pages\EditKategori;
use App\Filament\Resources\Kategoris\Pages\ListKategoris;
use App\Filament\Resources\Kategoris\Schemas\KategoriForm;
use App\Filament\Resources\Kategoris\Tables\KategorisTable;
use App\Models\KategoriBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KategoriResource extends Resource
{
    protected static ?string $model = KategoriBarang::class;

    protected static ?string $slug = 'kategori-barangs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $navigationLabel = 'Kategori';

    protected static string|UnitEnum|null $navigationGroup = 'Data Induk';

    protected static ?int $navigationSort = 13;

    protected static ?string $modelLabel = 'Kategori Barang';

    protected static ?string $pluralModelLabel = 'Kategori Barang';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function alasanTidakBisaDihapus(KategoriBarang $kategori): ?string
    {
        if ($kategori->barang()->exists() || $kategori->grade()->exists()) {
            return "Kategori {$kategori->kode} sudah dipakai pada master barang atau grade "
                .'sehingga tidak dapat dihapus.';
        }

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return KategoriForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategorisTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode', 'nama'];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKategoris::route('/'),
            'create' => CreateKategori::route('/create'),
            'edit' => EditKategori::route('/{record}/edit'),
        ];
    }
}
