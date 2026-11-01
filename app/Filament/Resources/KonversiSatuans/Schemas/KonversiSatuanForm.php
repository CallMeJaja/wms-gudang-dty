<?php

namespace App\Filament\Resources\KonversiSatuans\Schemas;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\KonversiSatuan;
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
 * Form master data Konversi Satuan (PRD §3.3, A1b).
 *
 * POY tidak boleh memiliki konversi: saldo POY wajib berasal dari
 * timbangan Kg, bukan hasil kali bobbin/box.
 */
class KonversiSatuanForm
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
                                Section::make('Aturan Konversi')
                                    ->description('Kosongkan barang untuk konversi global yang berlaku pada semua barang non-POY.')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('barang_id')
                                            ->label('Barang')
                                            ->relationship(
                                                name: 'barang',
                                                titleAttribute: 'kode_barang',
                                                modifyQueryUsing: fn (Builder $query) => $query
                                                    ->whereHas('kategori', fn (Builder $q) => $q->where('kode', '!=', KategoriBarang::POY))
                                                    ->orderBy('kode_barang'),
                                            )
                                            ->getOptionLabelFromRecordUsing(
                                                fn (Barang $record) => "{$record->kode_barang} — {$record->nama_barang}"
                                            )
                                            ->searchable(['kode_barang', 'nama_barang'])
                                            ->preload()
                                            ->nullable()
                                            ->placeholder('Global (semua barang)')
                                            ->helperText('Barang kategori POY tidak boleh dikonversi (A1b).')
                                            ->rules([
                                                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                                    if (! $value) {
                                                        return;
                                                    }

                                                    $barang = Barang::with('kategori')->find($value);

                                                    if ($barang?->kategori?->kode === KategoriBarang::POY) {
                                                        $fail('POY tidak boleh memiliki konversi satuan. Saldo POY wajib dihitung dari '
                                                            .'berat timbangan (Kg), bukan konversi bobbin/box (A1b).');
                                                    }
                                                },
                                            ]),

                                        Select::make('dari_satuan_id')
                                            ->label('Satuan Asal')
                                            ->relationship(
                                                name: 'dariSatuan',
                                                titleAttribute: 'nama',
                                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                            )
                                            ->getOptionLabelFromRecordUsing(
                                                fn (Satuan $record) => "{$record->kode} — {$record->nama}"
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('ke_satuan_id')
                                            ->label('Satuan Tujuan')
                                            ->relationship(
                                                name: 'keSatuan',
                                                titleAttribute: 'nama',
                                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                            )
                                            ->getOptionLabelFromRecordUsing(
                                                fn (Satuan $record) => "{$record->kode} — {$record->nama}"
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->different('dari_satuan_id')
                                            ->rules([
                                                fn (Get $get, ?KonversiSatuan $record): \Closure => function (
                                                    string $attribute,
                                                    mixed $value,
                                                    \Closure $fail,
                                                ) use ($get, $record): void {
                                                    $dari = $get('dari_satuan_id');

                                                    if (! $dari || ! $value) {
                                                        return;
                                                    }

                                                    // MySQL menganggap NULL berbeda pada unique index,
                                                    // sehingga duplikat global dicek manual di sini.
                                                    $duplikat = KonversiSatuan::query()
                                                        ->where('dari_satuan_id', $dari)
                                                        ->where('ke_satuan_id', $value)
                                                        ->when(
                                                            $get('barang_id'),
                                                            fn (Builder $q, $barangId) => $q->where('barang_id', $barangId),
                                                            fn (Builder $q) => $q->whereNull('barang_id'),
                                                        )
                                                        ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                                                        ->exists();

                                                    if ($duplikat) {
                                                        $fail('Kombinasi konversi (barang, satuan asal, satuan tujuan) sudah ada.');
                                                    }
                                                },
                                            ]),

                                        TextInput::make('faktor')
                                            ->label('Faktor')
                                            ->numeric()
                                            ->required()
                                            ->rule('gt:0')
                                            ->step(0.0001)
                                            ->suffix(fn (Get $get): string => Satuan::find($get('ke_satuan_id'))?->kode ?? 'rasio')
                                            ->helperText('1 satuan asal = faktor × satuan tujuan.'),
                                    ]),
                            ]),

                        Section::make('Default')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_default')
                                    ->label('Konversi Default')
                                    ->helperText('Dipakai sebagai pilihan awal saat mengonversi satuan.'),
                            ]),
                    ]),
            ]);
    }
}
