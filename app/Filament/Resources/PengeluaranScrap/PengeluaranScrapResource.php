<?php

namespace App\Filament\Resources\PengeluaranScrap;

use App\Enums\JenisKeluar;
use App\Filament\Concerns\InteractsWithPengeluaran;
use App\Filament\Resources\PengeluaranScrap\Pages\CreatePengeluaranScrap;
use App\Filament\Resources\PengeluaranScrap\Pages\EditPengeluaranScrap;
use App\Filament\Resources\PengeluaranScrap\Pages\ListPengeluaranScrap;
use App\Filament\Resources\PengeluaranScrap\Schemas\PengeluaranScrapForm;
use App\Filament\Resources\PengeluaranScrap\Tables\PengeluaranScrapTable;
use App\Models\TransaksiKeluar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PengeluaranScrapResource extends Resource
{
    use InteractsWithPengeluaran;

    protected static ?string $model = TransaksiKeluar::class;

    protected static ?string $slug = 'pengeluaran-scrap';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static ?string $navigationLabel = 'Scrap / Reject';

    protected static ?string $modelLabel = 'Pengeluaran Scrap';

    protected static ?string $pluralModelLabel = 'Pengeluaran Scrap';

    protected static string|UnitEnum|null $navigationGroup = 'Pengeluaran';

    protected static ?int $navigationSort = 112;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    protected static bool $shouldRegisterNavigation = false;

    public static function jenisKeluar(): JenisKeluar
    {
        return JenisKeluar::DtyScrapReject;
    }

    public static function form(Schema $schema): Schema
    {
        return PengeluaranScrapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengeluaranScrapTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengeluaranScrap::route('/'),
            'create' => CreatePengeluaranScrap::route('/create'),
            'edit' => EditPengeluaranScrap::route('/{record}/edit'),
        ];
    }
}
