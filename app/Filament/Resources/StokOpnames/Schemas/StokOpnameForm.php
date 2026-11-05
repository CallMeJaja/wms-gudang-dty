<?php

namespace App\Filament\Resources\StokOpnames\Schemas;

use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * Form opname stok.
 *
 * `nomor_opname` dan `selisih_qty` TIDAK ada di form karena dibuat oleh
 * service / generated column DB. Penyesuaian stok tetap sepenuhnya
 * ditangani StokOpnameService — form hanya mengumpulkan input fisik.
 */
class StokOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Posisi Stok')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('barang_id')
                                            ->label('Barang')
                                            ->options(fn () => Barang::query()
                                                ->aktif()
                                                ->orderBy('kode_barang')
                                                ->get()
                                                ->mapWithKeys(fn (Barang $barang): array => [
                                                    $barang->id => "{$barang->kode_barang} — {$barang->nama_barang}",
                                                ])
                                                ->all())
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Set $set) => $set('grade_id', null)),

                                        TextInput::make('merge')
                                            ->label('Merge')
                                            ->visible(fn (Get $get): bool => ! self::isPendukung($get('barang_id')))
                                            ->required(fn (Get $get): bool => ! self::isPendukung($get('barang_id')))
                                            ->maxLength(50)
                                            ->placeholder('4P1044')
                                            ->helperText('Wajib untuk POY/DTY; item pendukung memakai - otomatis.'),

                                        Select::make('grade_id')
                                            ->label('Grade')
                                            ->options(fn (Get $get) => Grade::query()
                                                ->aktif()
                                                ->when(
                                                    $get('barang_id'),
                                                    fn ($query, $barangId) => $query->where(
                                                        'kategori_id',
                                                        Barang::query()->find($barangId)?->kategori_id,
                                                    ),
                                                )
                                                ->orderBy('kode')
                                                ->pluck('kode', 'id')
                                                ->all())
                                            ->searchable()
                                            ->visible(fn (Get $get): bool => ! self::isPendukung($get('barang_id')))
                                            ->required(fn (Get $get): bool => ! self::isPendukung($get('barang_id'))),
                                    ]),

                                Section::make('Hasil Hitung')
                                    ->description('Masukkan jumlah fisik dan tanggal opname.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('stok_fisik_qty')
                                            ->label('Stok Fisik')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->step('0.01')
                                            ->suffix('kg'),

                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Opname')
                                            ->required()
                                            ->default(fn () => now()->endOfMonth())
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->helperText('Hanya boleh tanggal terakhir bulan.'),
                                    ]),
                            ]),

                        Section::make('Catatan')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                                    ->maxLength(255)
                                    ->placeholder('Opsional')
                                    ->helperText('Selisih dihitung otomatis oleh sistem saat disimpan.'),
                            ]),
                    ]),
            ]);
    }

    private static function isPendukung(?int $barangId): bool
    {
        return $barangId !== null
            && Barang::query()->find($barangId)?->kategori?->kode === KategoriBarang::PENDUKUNG;
    }
}
