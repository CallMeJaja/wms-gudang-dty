<?php

namespace App\Filament\Resources\Grades\Schemas;

use App\Filament\Resources\Grades\GradeResource;
use App\Models\Grade;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

/**
 * Form master data Grade.
 *
 * Grade bersifat tertutup dan unik per kategori. Grade C kategori DTY
 * dikunci: kode, kategori, dan status aktif tidak dapat diubah.
 */
class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        $dilindungi = fn (?Grade $record): bool => $record !== null && GradeResource::dilindungi($record);

        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Identitas Grade')
                                    ->description('Kode grade unik di dalam satu kategori.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('kode')
                                            ->label('Kode Grade')
                                            ->required()
                                            ->maxLength(10)
                                            ->unique(
                                                ignoreRecord: true,
                                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule
                                                    ->where('kategori_id', $get('kategori_id')),
                                            )
                                            ->disabled($dilindungi)
                                            ->helperText(fn (?Grade $record): string => $dilindungi($record)
                                                ? 'Grade C kategori DTY adalah grade scrap dan tidak dapat diubah.'
                                                : 'Contoh: A, B, C, AL.'),

                                        Select::make('kategori_id')
                                            ->label('Kategori')
                                            ->relationship(
                                                name: 'kategori',
                                                titleAttribute: 'nama',
                                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                            )
                                            ->getOptionLabelFromRecordUsing(
                                                fn ($record) => "{$record->kode} — {$record->nama}"
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->disabled($dilindungi),

                                        TextInput::make('keterangan')
                                            ->label('Keterangan')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Section::make('Status')
                            ->description('Grade C kategori DTY adalah grade scrap dan tidak dapat diubah.')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->disabled($dilindungi)
                                    ->helperText('Grade nonaktif tidak muncul pada pilihan transaksi.'),
                            ]),
                    ]),
            ]);
    }
}
