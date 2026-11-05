<?php

namespace App\Filament\Resources\SuratJalanDty;

use App\Enums\JenisKeluar;
use App\Filament\Concerns\InteractsWithPengeluaran;
use App\Filament\Resources\SuratJalanDty\Pages\CetakSuratJalan;
use App\Filament\Resources\SuratJalanDty\Pages\CreateSuratJalanDty;
use App\Filament\Resources\SuratJalanDty\Pages\EditSuratJalanDty;
use App\Filament\Resources\SuratJalanDty\Pages\ListSuratJalanDty;
use App\Filament\Resources\SuratJalanDty\Schemas\SuratJalanDtyForm;
use App\Filament\Resources\SuratJalanDty\Tables\SuratJalanDtyTable;
use App\Models\TransaksiKeluar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SuratJalanDtyResource extends Resource
{
    use InteractsWithPengeluaran;

    protected static ?string $model = TransaksiKeluar::class;

    protected static ?string $slug = 'surat-jalan-dty';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'DTY (Surat Jalan)';

    protected static ?string $modelLabel = 'Pengeluaran DTY (Surat Jalan)';

    protected static ?string $pluralModelLabel = 'Pengeluaran DTY (Surat Jalan)';

    protected static string|UnitEnum|null $navigationGroup = 'Pengeluaran';

    protected static ?int $navigationSort = 110;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    public static function jenisKeluar(): JenisKeluar
    {
        return JenisKeluar::DtySuratJalan;
    }

    public static function form(Schema $schema): Schema
    {
        return SuratJalanDtyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuratJalanDtyTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuratJalanDty::route('/'),
            'create' => CreateSuratJalanDty::route('/create'),
            'edit' => EditSuratJalanDty::route('/{record}/edit'),
            'cetak' => CetakSuratJalan::route('/{record}/cetak'),
        ];
    }
}
