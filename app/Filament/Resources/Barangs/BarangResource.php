<?php

namespace App\Filament\Resources\Barangs;

use App\Filament\Resources\Barangs\Pages\CreateBarang;
use App\Filament\Resources\Barangs\Pages\EditBarang;
use App\Filament\Resources\Barangs\Pages\ListBarangs;
use App\Filament\Resources\Barangs\Schemas\BarangForm;
use App\Filament\Resources\Barangs\Tables\BarangsTable;
use App\Models\Barang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Barang';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Barang';

    protected static ?string $pluralModelLabel = 'Barang';

    protected static ?string $recordTitleAttribute = 'nama_barang';

    public static function form(Schema $schema): Schema
    {
        return BarangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BarangsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_barang', 'nama_barang'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return (string) $record->kode_barang;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Nama' => (string) $record->nama_barang,
            'Kategori' => $record->kategori?->kode ?? '—',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index', ['tableSearch' => $record->kode_barang]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBarangs::route('/'),
            'create' => CreateBarang::route('/create'),
            'edit' => EditBarang::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
