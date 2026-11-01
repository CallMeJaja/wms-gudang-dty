<?php

namespace App\Filament\Resources\Barangs\Schemas;

use App\Models\KategoriBarang;
use App\Models\Satuan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Form master data Barang (PRD §3.1, A11).
 *
 * Aturan bisnis yang dipertahankan dari BarangRequest:
 * `is_merge_minggu` hanya boleh aktif untuk kategori POY (A11).
 */
class BarangForm
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
                                Section::make('Identitas Barang')
                                    ->description('Kode dan nama barang sesuai master gudang.')
                                    ->icon(Heroicon::OutlinedCube)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('kode_barang')
                                            ->label('Kode Barang')
                                            ->required()
                                            ->maxLength(50)
                                            ->unique(ignoreRecord: true)
                                            ->prefixIcon('heroicon-o-qr-code')
                                            ->helperText('Unik. Contoh: POY-150-48, DTY-150-48.'),

                                        TextInput::make('nama_barang')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->maxLength(150),

                                        Select::make('kategori_id')
                                            ->label('Kategori')
                                            ->relationship(
                                                name: 'kategori',
                                                titleAttribute: 'nama',
                                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                            )
                                            ->getOptionLabelFromRecordUsing(
                                                fn (KategoriBarang $record) => "{$record->kode} — {$record->nama}"
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),

                                        Select::make('satuan_dasar_id')
                                            ->label('Satuan Dasar')
                                            ->relationship(
                                                name: 'satuanDasar',
                                                titleAttribute: 'nama',
                                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                            )
                                            ->getOptionLabelFromRecordUsing(
                                                fn ($record) => "{$record->kode} — {$record->nama}"
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),

                                Section::make('Spesifikasi Benang')
                                    ->description('Denier dan filament hanya relevan untuk POY/DTY.')
                                    ->icon(Heroicon::OutlinedScale)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('denier')
                                            ->label('Denier')
                                            ->maxLength(20)
                                            ->placeholder('150'),

                                        TextInput::make('filament')
                                            ->label('Filament')
                                            ->maxLength(20)
                                            ->placeholder('48'),
                                    ]),

                                Section::make('Nilai Stok')
                                    ->icon(Heroicon::OutlinedChartBar)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('stok_minimum')
                                            ->label('Stok Minimum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->suffix(fn (Get $get) => self::kodeSatuan($get('satuan_dasar_id')))
                                            ->helperText('Dipakai untuk notifikasi stok menipis.'),
                                    ]),
                            ]),

                        Section::make('Status & Buffer')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('Barang nonaktif tidak muncul di pilihan transaksi.'),

                                Toggle::make('is_merge_minggu')
                                    ->label('Merge Minggu (Buffer)')
                                    ->helperText('Hanya untuk kategori POY. Buffer Minggu tidak berlaku untuk DTY/pendukung (A11).')
                                    ->disabled(fn (Get $get) => self::kodeKategori($get('kategori_id')) !== KategoriBarang::POY)
                                    ->dehydrated()
                                    // A11: jangan pernah simpan true untuk non-POY.
                                    ->afterStateUpdated(function (Toggle $component, Get $get, ?bool $state) {
                                        if ($state && self::kodeKategori($get('kategori_id')) !== KategoriBarang::POY) {
                                            $component->state(false);
                                        }
                                    })
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if (! $value) {
                                                return;
                                            }

                                            $kode = self::kodeKategori($get('kategori_id'));

                                            if ($kode !== null && $kode !== KategoriBarang::POY) {
                                                $fail("Penandaan \"Merge Minggu\" hanya berlaku untuk barang berkategori POY. Barang kategori {$kode} tidak boleh disisihkan ke buffer Minggu (A11).");
                                            }
                                        },
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function kodeKategori(?int $kategoriId): ?string
    {
        return $kategoriId ? KategoriBarang::find($kategoriId)?->kode : null;
    }

    private static function kodeSatuan(?int $satuanId): ?string
    {
        return $satuanId ? Satuan::find($satuanId)?->kode : null;
    }
}
