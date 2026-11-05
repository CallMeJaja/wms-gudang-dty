<?php

namespace App\Filament\Resources\WasteLogs;

use App\Filament\Resources\WasteLogs\Pages\CreateWasteLog;
use App\Filament\Resources\WasteLogs\Pages\EditWasteLog;
use App\Filament\Resources\WasteLogs\Pages\ListWasteLogs;
use App\Filament\Resources\WasteLogs\Schemas\WasteLogForm;
use App\Filament\Resources\WasteLogs\Tables\WasteLogsTable;
use App\Models\WasteLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class WasteLogResource extends Resource
{
    protected static ?string $model = WasteLog::class;

    protected static ?string $slug = 'waste-logs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static ?string $navigationLabel = 'Waste / Scrap';

    protected static ?string $modelLabel = 'Waste / Scrap';

    protected static ?string $pluralModelLabel = 'Waste / Scrap';

    protected static string|UnitEnum|null $navigationGroup = 'Persediaan';

    protected static ?int $navigationSort = 122;

    public static function form(Schema $schema): Schema
    {
        return WasteLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WasteLogsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['tujuan', 'keterangan'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Waste / Scrap #'.$record->getKey();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tanggal' => $record->tanggal?->format('d-m-Y') ?? '—',
            'Jenis' => $record->labelJenis(),
            'Tujuan' => $record->tujuan ?? '—',
            'Berat' => number_format((float) $record->berat_kg, 2, ',', '.').' kg',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWasteLogs::route('/'),
            'create' => CreateWasteLog::route('/create'),
            'edit' => EditWasteLog::route('/{record}/edit'),
        ];
    }
}
