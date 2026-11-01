<?php

namespace App\Filament\Resources\StokBarangs;

use App\Filament\Resources\StokBarangs\Pages\ListStokBarangs;
use App\Filament\Resources\StokBarangs\Tables\StokBarangsTable;
use App\Models\StokBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StokBarangResource extends Resource
{
    protected static ?string $model = StokBarang::class;

    protected static ?string $slug = 'stok-barangs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Stok Barang';

    protected static string|UnitEnum|null $navigationGroup = 'Persediaan';

    protected static ?int $navigationSort = 120;

    protected static ?string $modelLabel = 'Stok Barang';

    protected static ?string $pluralModelLabel = 'Stok Barang';

    protected static ?string $recordTitleAttribute = 'merge';

    public static function table(Table $table): Table
    {
        return StokBarangsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['barang.kategori', 'grade']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStokBarangs::route('/'),
        ];
    }
}
