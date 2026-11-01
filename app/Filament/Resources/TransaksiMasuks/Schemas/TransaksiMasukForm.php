<?php

namespace App\Filament\Resources\TransaksiMasuks\Schemas;

use App\Enums\JenisMasuk;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\SupplierCustomer;
use App\Models\TransaksiMasukDetail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Form Penerimaan (Inbound) — versi Filament.
 *
 * Menggantikan `resources/views/inbound/_form.blade.php` (487 baris) +
 * PenerimaanPoyController. Pola: header transaksi + repeater baris detail
 * yang terikat langsung ke relasi `detail` (HasMany).
 */
class TransaksiMasukForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Dokumen')
                    ->columns(3)
                    ->schema([
                        Select::make('jenis_masuk')
                            ->label('Jenis Penerimaan')
                            ->options(collect(JenisMasuk::cases())
                                ->mapWithKeys(fn (JenisMasuk $j) => [$j->value => $j->label()])
                                ->all())
                            ->required()
                            ->native(false),

                        TextInput::make('nomor_transaksi')
                            ->label('Nomor Transaksi')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Otomatis saat posting')
                            ->helperText('Dibuat otomatis oleh sistem (PRD §7.1).'),

                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        TextInput::make('nomor_gate_pass')
                            ->label('Nomor Gate Pass')
                            ->maxLength(100),

                        Select::make('partner_id')
                            ->label('Supplier / Partner')
                            ->relationship(
                                name: 'partner',
                                titleAttribute: 'nama',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('nama'),
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('shift')
                            ->label('Shift')
                            ->options([1 => 'Shift 1', 2 => 'Shift 2', 3 => 'Shift 3'])
                            ->native(false),

                        TextInput::make('pengirim')
                            ->label('Pengirim')
                            ->maxLength(100),

                        TextInput::make('penerima')
                            ->label('Penerima')
                            ->maxLength(100),

                        TextInput::make('ref_dokumen')
                            ->label('Ref. Dokumen')
                            ->maxLength(100),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade. Total KG dihitung otomatis.')
                    ->schema([
                        Repeater::make('detail')
                            ->relationship()
                            ->label('')
                            ->addActionLabel('Tambah baris')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => self::labelBaris($state))
                            ->table([
                                TableColumn::make('Barang'),
                                TableColumn::make('Merge'),
                                TableColumn::make('Grade'),
                                TableColumn::make('Box'),
                                TableColumn::make('Bobbin'),
                                TableColumn::make('Berat (KG)'),
                            ])
                            ->schema([
                                Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship(
                                        name: 'barang',
                                        titleAttribute: 'nama_barang',
                                        modifyQueryUsing: fn (Builder $query) => $query
                                            ->where('is_active', true)
                                            ->orderBy('kode_barang'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Barang $record) => "{$record->kode_barang} — {$record->nama_barang}"
                                    )
                                    ->searchable(['kode_barang', 'nama_barang'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('grade_id', null)),

                                TextInput::make('merge')
                                    ->label('Merge')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('A-01'),

                                Select::make('grade_id')
                                    ->label('Grade')
                                    ->relationship(
                                        name: 'grade',
                                        titleAttribute: 'kode',
                                        modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                            ->when($get('barang_id'), function (Builder $q, $barangId) {
                                                $kategoriId = Barang::find($barangId)?->kategori_id;

                                                $q->where('kategori_id', $kategoriId);
                                            })
                                            ->orderBy('kode'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Grade $record) => $record->kode)
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('jumlah_kemasan')
                                    ->label('Box')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(null),

                                TextInput::make('jumlah_bobbin')
                                    ->label('Bobbin')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(null),

                                TextInput::make('berat_kg')
                                    ->label('Berat (KG)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step('0.01')
                                    ->suffix('kg')
                                    ->live(onBlur: true),

                                Select::make('satuan_kemasan_id')
                                    ->label('Satuan Kemasan')
                                    ->relationship(
                                        name: 'satuanKemasan',
                                        titleAttribute: 'nama',
                                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->default(null)
                                    ->columnSpan(1),

                                Textarea::make('keterangan')
                                    ->label('Keterangan / Batch')
                                    ->rows(2)
                                    ->maxLength(255),
                            ])
                            ->columns(6)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->required(),

                        // Total berjalan — dihitung dari state repeater.
                        TextInput::make('total_berat_kg')
                            ->label('Total Berat Masuk')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('kg')
                            ->helperText('Akumulasi Net Weight seluruh baris.')
                            ->afterStateHydrated(function (TextInput $component, Get $get) {
                                $component->state(self::hitungTotal($get('detail')));
                            })
                            ->formatStateUsing(fn ($state) => is_numeric($state)
                                ? number_format((float) $state, 2, ',', '.')
                                : $state),
                    ]),

                Section::make('Catatan')
                    ->collapsed()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Transaksi')
                            ->rows(3)
                            ->maxLength(500),
                    ]),
            ]);
    }

    /**
     * Jumlahkan berat_kg dari seluruh baris repeater.
     */
    private static function hitungTotal(mixed $detail): float
    {
        if (! is_array($detail)) {
            return 0.0;
        }

        return round(array_sum(array_map(
            fn ($baris) => (float) ($baris['berat_kg'] ?? 0),
            $detail,
        )), 2);
    }

    /**
     * Judul ringkas tiap baris agar mudah dibaca saat collapsed.
     */
    private static function labelBaris(array $state): ?string
    {
        $barang = isset($state['barang_id'])
            ? Barang::find($state['barang_id'])?->kode_barang
            : null;

        if (! $barang) {
            return null;
        }

        $merge = $state['merge'] ?? null;
        $berat = isset($state['berat_kg']) ? number_format((float) $state['berat_kg'], 2, ',', '.') : null;

        return trim($barang.($merge ? " / {$merge}" : '').($berat ? " — {$berat} kg" : ''));
    }
}
