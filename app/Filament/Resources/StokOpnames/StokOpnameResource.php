<?php

namespace App\Filament\Resources\StokOpnames;

use App\Filament\Resources\StokOpnames\Pages\CreateStokOpname;
use App\Filament\Resources\StokOpnames\Pages\ListStokOpnames;
use App\Filament\Resources\StokOpnames\Schemas\StokOpnameForm;
use App\Filament\Resources\StokOpnames\Tables\StokOpnamesTable;
use App\Models\StokOpname;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StokOpnameResource extends Resource
{
    protected static ?string $model = StokOpname::class;

    protected static ?string $slug = 'stok-opnames';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Stok Opname';

    protected static string|UnitEnum|null $navigationGroup = 'Persediaan';

    protected static ?int $navigationSort = 121;

    protected static ?string $modelLabel = 'Stok Opname';

    protected static ?string $pluralModelLabel = 'Stok Opname';

    protected static ?string $recordTitleAttribute = 'nomor_opname';

    public static function form(Schema $schema): Schema
    {
        return StokOpnameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StokOpnamesTable::configure($table);
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
            'index' => ListStokOpnames::route('/'),
            'create' => CreateStokOpname::route('/create'),
        ];
    }
}
