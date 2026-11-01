<?php

namespace App\Filament\Resources\PenerimaanPendukung;

use App\Enums\JenisMasuk;
use App\Filament\Concerns\InteractsWithPenerimaan;
use App\Filament\Resources\PenerimaanPendukung\Pages\CetakGatePass;
use App\Filament\Resources\PenerimaanPendukung\Pages\CreatePenerimaanPendukung;
use App\Filament\Resources\PenerimaanPendukung\Pages\EditPenerimaanPendukung;
use App\Filament\Resources\PenerimaanPendukung\Pages\ListPenerimaanPendukung;
use App\Filament\Resources\PenerimaanPendukung\Schemas\PenerimaanPendukungForm;
use App\Filament\Resources\PenerimaanPendukung\Tables\PenerimaanPendukungTable;
use App\Models\TransaksiMasuk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PenerimaanPendukungResource extends Resource
{
    use InteractsWithPenerimaan;

    protected static ?string $model = TransaksiMasuk::class;

    protected static ?string $slug = 'penerimaan-pendukung';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Gate Pass Pendukung';

    protected static ?string $modelLabel = 'Gate Pass Pendukung';

    protected static ?string $pluralModelLabel = 'Gate Pass Pendukung';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi Masuk';

    protected static ?int $navigationSort = 101;

    protected static ?string $recordTitleAttribute = 'nomor_gate_pass';

    public static function jenisMasuk(): JenisMasuk
    {
        return JenisMasuk::PendukungGatepass;
    }

    public static function form(Schema $schema): Schema
    {
        return PenerimaanPendukungForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenerimaanPendukungTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPenerimaanPendukung::route('/'),
            'create' => CreatePenerimaanPendukung::route('/create'),
            'edit' => EditPenerimaanPendukung::route('/{record}/edit'),
            'cetak' => CetakGatePass::route('/{record}/cetak'),
        ];
    }
}
