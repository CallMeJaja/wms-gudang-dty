<?php

namespace App\Filament\Resources\Grades;

use App\Filament\Resources\Grades\Pages\CreateGrade;
use App\Filament\Resources\Grades\Pages\EditGrade;
use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Filament\Resources\Grades\Schemas\GradeForm;
use App\Filament\Resources\Grades\Tables\GradesTable;
use App\Models\Grade;
use App\Models\KategoriBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $slug = 'grades';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Grade';

    protected static string|UnitEnum|null $navigationGroup = 'Data Induk';

    protected static ?int $navigationSort = 12;

    protected static ?string $modelLabel = 'Grade';

    protected static ?string $pluralModelLabel = 'Grade';

    protected static ?string $recordTitleAttribute = 'kode';

    /**
     * Grade C pada kategori DTY adalah scrap yang wajib dipertahankan
     * (kode & kategori tidak boleh diubah, tidak boleh nonaktif/dihapus).
     */
    public static function dilindungi(Grade $grade): bool
    {
        return $grade->kode === Grade::KODE_SCRAP
            && $grade->kategori?->kode === KategoriBarang::DTY;
    }

    public static function alasanTidakBisaDihapus(Grade $grade): ?string
    {
        if (static::dilindungi($grade)) {
            return 'Grade C pada kategori DTY adalah grade scrap yang wajib dipertahankan '
                .'(konfirmasi PIC: scrap grade C pernah keluar). Grade ini tidak dapat dihapus.';
        }

        if ($grade->stokBarang()->exists()) {
            return "Grade {$grade->kode} sudah dipakai pada stok barang sehingga tidak dapat dihapus. "
                .'Nonaktifkan saja bila tidak dipakai lagi.';
        }

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return GradeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradesTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode', 'keterangan'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kategori' => $record->kategori?->kode,
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGrades::route('/'),
            'create' => CreateGrade::route('/create'),
            'edit' => EditGrade::route('/{record}/edit'),
        ];
    }
}
