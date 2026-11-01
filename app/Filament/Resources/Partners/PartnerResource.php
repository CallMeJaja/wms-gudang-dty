<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\Pages\ListPartners;
use App\Filament\Resources\Partners\Schemas\PartnerForm;
use App\Filament\Resources\Partners\Tables\PartnersTable;
use App\Models\SupplierCustomer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PartnerResource extends Resource
{
    protected static ?string $model = SupplierCustomer::class;

    protected static ?string $slug = 'partners';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Partner';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 11;

    protected static ?string $modelLabel = 'Partner';

    protected static ?string $pluralModelLabel = 'Partner';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnersTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode', 'nama', 'kontak'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return (string) $record->kode;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Nama' => (string) $record->nama,
            'Jenis' => (string) $record->jenis,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index', ['tableSearch' => $record->kode]);
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
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
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
