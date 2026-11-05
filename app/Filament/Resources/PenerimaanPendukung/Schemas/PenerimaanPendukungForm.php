<?php

namespace App\Filament\Resources\PenerimaanPendukung\Schemas;

use App\Enums\JenisMasuk;
use App\Filament\Support\PenerimaanDetailMapper;
use App\Filament\Support\PenerimaanForm;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Services\NomorDokumenService;
use App\Services\ShiftService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Form Penerimaan Item Pendukung / Gate Pass.
 *
 * Item pendukung tidak memakai grade (grade netral STD diisi mapper) dan
 * berat KG dihitung server dari Qty x satuan kemasan.
 */
class PenerimaanPendukungForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identitas Dokumen')
                            ->description('Dokumen resmi Gate Pass. Nomor dapat diisi manual sesuai dokumen fisik IPCI.')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_gate_pass')
                                    ->label('Nomor Gate Pass')
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->default(fn () => app(NomorDokumenService::class)->peek('GATE_PASS'))
                                    ->helperText('Kosongkan untuk generate otomatis.'),

                                TextInput::make('nomor_transaksi')
                                    ->label('Nomor Transaksi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Digenerate otomatis saat disimpan.'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Terima')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),

                                Select::make('partner_id')
                                    ->label('Pemasok / Mitra')
                                    ->relationship(
                                        name: 'partner',
                                        titleAttribute: 'nama',
                                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('nama'),
                                    )
                                    ->searchable()
                                    ->preload(),

                                Select::make('shift')
                                    ->label('Shift')
                                    ->options(PenerimaanForm::opsiShift())
                                    ->default(fn (): int => app(ShiftService::class)->current())
                                    ->native(false),

                                TextInput::make('operator_nama')
                                    ->label('Nama Operator')
                                    ->maxLength(100),

                                TextInput::make('ref_dokumen')
                                    ->label('Ref. Dokumen')
                                    ->maxLength(100),

                                TextInput::make('pengirim')
                                    ->label('Pengirim')
                                    ->maxLength(100),

                                TextInput::make('penerima')
                                    ->label('Penerima')
                                    ->maxLength(100),
                            ]),
                        Section::make('Ringkasan')
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('jenis_dokumen')
                                    ->label('Jenis Dokumen')
                                    ->content(JenisMasuk::PendukungGatepass->label()),
                                Placeholder::make('status_dokumen')
                                    ->label('Status')
                                    ->content('Draf'),
                                Placeholder::make('total_berat')
                                    ->label('Total Berat')
                                    ->content(fn (Get $get): string => number_format(PenerimaanForm::totalBerat($get('detail')), 2, ',', '.').' kg'),
                                Placeholder::make('alur_stok')
                                    ->label('Alur Stok')
                                    ->content('Stok belum berubah. Stok bertambah setelah Terbitkan.'),
                            ]),
                    ]),

                Section::make('Rincian Item')
                    ->description('Qty x satuan kemasan otomatis dikonversi ke KG oleh sistem.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detail')
                            ->relationship()
                            ->label('')
                            ->addActionLabel('Tambah baris')
                            ->addActionAlignment(Alignment::End)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->collapsible()
                            ->reorderable(false)
                            ->columns(['default' => 1, 'md' => 3])
                            ->itemLabel(fn (array $state): ?string => self::labelBaris($state))
                            ->mutateRelationshipDataBeforeCreateUsing(
                                fn (array $data): array => PenerimaanDetailMapper::pendukung($data)
                            )
                            ->mutateRelationshipDataBeforeSaveUsing(
                                fn (array $data): array => PenerimaanDetailMapper::pendukung($data)
                            )
                            ->schema([
                                Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship(
                                        name: 'barang',
                                        titleAttribute: 'nama_barang',
                                        modifyQueryUsing: fn (Builder $query) => $query
                                            ->where('is_active', true)
                                            ->whereHas('kategori', fn (Builder $q) => $q->where('kode', KategoriBarang::PENDUKUNG))
                                            ->orderBy('kode_barang'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Barang $record) => "{$record->kode_barang} — {$record->nama_barang}"
                                    )
                                    ->searchable(['kode_barang', 'nama_barang'])
                                    ->preload()
                                    ->placeholder('Pilih')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                        $barang = Barang::query()->find($state);
                                        $set('satuan_kemasan_id', $barang?->satuan_dasar_id);
                                        self::hitungBerat($set, $get);
                                    }),

                                TextInput::make('batch')
                                    ->label('Batch (opsional)')
                                    ->maxLength(50)
                                    ->placeholder('GRN-VELVET')
                                    ->helperText('Batch hanya informasi; stok item pendukung selalu memakai merge -.'),

                                TextInput::make('jumlah_kemasan')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step('0.01')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungBerat($set, $get)),

                                Select::make('satuan_kemasan_id')
                                    ->label('Satuan')
                                    ->relationship(
                                        name: 'satuanKemasan',
                                        titleAttribute: 'kode',
                                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Satuan $record) => "{$record->kode} — {$record->nama}"
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::hitungBerat($set, $get)),

                                TextInput::make('berat_kg')
                                    ->label('Berat (KG)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('kg'),

                                TextInput::make('keterangan')
                                    ->label('Catatan Baris')
                                    ->maxLength(255)
                                    ->placeholder('Catatan tambahan (opsional)'),
                            ]),
                    ]),

                Section::make('Catatan')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Transaksi')
                            ->rows(3)
                            ->maxLength(2000),
                    ]),
            ]);
    }

    /**
     * Pratinjau berat di form. Kesalahan konversi tidak memblokir input —
     * validasi final tetap dijalankan mapper saat menyimpan.
     */
    private static function hitungBerat(Set $set, Get $get): void
    {
        try {
            $set('berat_kg', PenerimaanDetailMapper::beratPendukung([
                'barang_id' => $get('barang_id'),
                'jumlah_kemasan' => $get('jumlah_kemasan'),
                'satuan_kemasan_id' => $get('satuan_kemasan_id'),
            ]));
        } catch (ValidationException) {
            $set('berat_kg', null);
        }
    }

    /**
     * Judul ringkas tiap baris agar mudah dibaca saat repeater dilipat.
     */
    private static function labelBaris(array $state): ?string
    {
        $barang = isset($state['barang_id'])
            ? Barang::query()->find($state['barang_id'])?->kode_barang
            : null;

        if ($barang === null) {
            return null;
        }

        $qty = isset($state['jumlah_kemasan']) && is_numeric($state['jumlah_kemasan'])
            ? rtrim(rtrim(number_format((float) $state['jumlah_kemasan'], 2, ',', '.'), '0'), ',')
            : null;
        $satuan = isset($state['satuan_kemasan_id'])
            ? Satuan::query()->find($state['satuan_kemasan_id'])?->kode
            : null;

        return trim($barang.($qty ? " — {$qty} {$satuan}" : ''));
    }
}
