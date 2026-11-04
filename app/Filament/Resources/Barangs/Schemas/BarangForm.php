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
use Illuminate\Database\Eloquent\Builder;

/**
 * Form master data Barang.
 *
 * `is_merge_minggu` hanya boleh aktif untuk kategori POY: buffer Minggu
 * menyisihkan POY, sedangkan DTY dan barang pendukung tidak memakainya.
 */
class BarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Barang')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-qr-code')
                            ->helperText('Unik. Contoh: POY-150-48, DTY-150-48.'),

                        TextInput::make('kode_material')
                            ->label('Kode Material')
                            ->maxLength(30)
                            ->unique(ignoreRecord: true)
                            ->helperText('Kode material IPCI (opsional).'),

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
                    ->columnSpanFull()
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

                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Nilai Stok')
                            ->columnSpan(2)
                            ->columns(1)
                            ->schema([
                                TextInput::make('stok_minimum')
                                    ->label('Stok Minimum')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix(fn (Get $get) => self::kodeSatuan($get('satuan_dasar_id')))
                                    ->helperText('Dipakai untuk notifikasi stok menipis.'),
                            ]),

                        Section::make('Status & Buffer')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('Barang nonaktif tidak muncul di pilihan transaksi.'),

                                Toggle::make('is_merge_minggu')
                                    ->label('Merge Minggu (Buffer)')
                                    ->helperText('Hanya untuk kategori POY. Buffer Minggu tidak berlaku untuk DTY/pendukung.')
                                    ->disabled(fn (Get $get) => self::kodeKategori($get('kategori_id')) !== KategoriBarang::POY)
                                    ->dehydrated()
                                    // Jangan pernah simpan true untuk barang non-POY.
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
                                                $fail("Penandaan \"Merge Minggu\" hanya berlaku untuk barang berkategori POY. Barang kategori {$kode} tidak boleh disisihkan ke buffer Minggu.");
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
