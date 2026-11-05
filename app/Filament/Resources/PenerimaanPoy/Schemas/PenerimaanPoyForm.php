<?php

namespace App\Filament\Resources\PenerimaanPoy\Schemas;

use App\Enums\JenisMasuk;
use App\Filament\Support\PenerimaanDetailMapper;
use App\Filament\Support\PenerimaanForm;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\SupplierCustomer;
use App\Models\TransaksiMasuk;
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

/**
 * Form Penerimaan POY dari IPCI.
 *
 * POY memakai berat timbangan (Kg). Box/Pallet dan Bobbin saling mengisi
 * otomatis sebagai bantuan input; nilai aktual tetap dapat diubah manual.
 */
class PenerimaanPoyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identitas Dokumen')
                            ->description('Penerimaan bahan baku POY dari IPCI. Nomor transaksi dibuat otomatis.')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_transaksi')
                                    ->label('Nomor Transaksi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(fn () => app(NomorDokumenService::class)->peekUnik(
                                        'TRX_MASUK',
                                        fn (string $n): bool => TransaksiMasuk::query()->where('nomor_transaksi', $n)->exists(),
                                    ))
                                    ->helperText('Digenerate otomatis saat disimpan.'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Terima')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),

                                Select::make('shift')
                                    ->label('Shift')
                                    ->options(PenerimaanForm::opsiShift())
                                    ->default(fn (): int => app(ShiftService::class)->current())
                                    ->required()
                                    ->native(false),

                                TextInput::make('operator_nama')
                                    ->label('Nama Operator')
                                    ->required()
                                    ->maxLength(100)
                                    ->helperText('Akun dipakai bersama per shift.'),

                                Select::make('partner_id')
                                    ->label('Pemasok / Mitra')
                                    ->relationship(
                                        name: 'partner',
                                        titleAttribute: 'nama',
                                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('nama'),
                                    )
                                    ->default(fn () => SupplierCustomer::query()->where('kode', 'IPCI')->value('id'))
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('ref_dokumen')
                                    ->label('No. Dokumen Sumber (opsional)')
                                    ->maxLength(100)
                                    ->helperText('Nomor DO/surat jalan IPCI; tidak wajib.'),

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
                                    ->content(JenisMasuk::PoyIpci->label()),
                                Placeholder::make('status_dokumen')
                                    ->label('Status')
                                    ->content('Draf'),
                                Placeholder::make('total_berat')
                                    ->label('Total Berat')
                                    ->content(fn (Get $get): string => number_format(PenerimaanForm::totalBerat($get('detail')), 2, ',', '.').' kg'),
                                Placeholder::make('alur_stok')
                                    ->label('Alur Stok')
                                    ->content('Stok belum berubah. Stok bertambah setelah Posting.'),
                            ]),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade. Total KG dihitung otomatis.')
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
                                fn (array $data): array => PenerimaanDetailMapper::poy($data)
                            )
                            ->mutateRelationshipDataBeforeSaveUsing(
                                fn (array $data): array => PenerimaanDetailMapper::poy($data)
                            )
                            ->schema([
                                Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship(
                                        name: 'barang',
                                        titleAttribute: 'nama_barang',
                                        modifyQueryUsing: fn (Builder $query) => $query
                                            ->where('is_active', true)
                                            ->whereHas('kategori', fn (Builder $q) => $q->where('kode', KategoriBarang::POY))
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
                                    ->afterStateUpdated(fn (Set $set) => $set('grade_id', null)),

                                TextInput::make('merge')
                                    ->label('Merge')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('4P1044'),

                                Select::make('grade_id')
                                    ->label('Grade')
                                    ->relationship(
                                        name: 'grade',
                                        titleAttribute: 'kode',
                                        modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                            ->where('is_active', true)
                                            ->when(
                                                $get('barang_id'),
                                                fn (Builder $q, $barangId) => $q->where(
                                                    'kategori_id',
                                                    Barang::query()->find($barangId)?->kategori_id,
                                                ),
                                            )
                                            ->orderBy('kode'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih')
                                    ->required(),

                                TextInput::make('jumlah_kemasan')
                                    ->label('Box / Pallet')
                                    ->numeric()
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->helperText('1 box/pallet = 36 bobbin = 576 kg')
                                    ->afterStateUpdated(function (Set $set, $state): void {
                                        $box = (float) $state;

                                        if ($box <= 0) {
                                            return;
                                        }

                                        $set('jumlah_bobbin', (int) round($box * (float) config('wms.satuan.poy_pallet_bobbin')));
                                        $set('berat_kg', round($box * (float) config('wms.satuan.poy_pallet_kg'), 2));
                                    }),

                                TextInput::make('jumlah_bobbin')
                                    ->label('Bobbin')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, $state): void {
                                        $bobbin = (float) $state;

                                        if ($bobbin <= 0) {
                                            return;
                                        }

                                        $set('jumlah_kemasan', round($bobbin / (float) config('wms.satuan.poy_pallet_bobbin'), 2));
                                        $set('berat_kg', round($bobbin * (float) config('wms.satuan.poy_bobbin_kg'), 2));
                                    }),

                                TextInput::make('berat_kg')
                                    ->label('Berat (KG)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step('0.01')
                                    ->suffix('kg')
                                    ->helperText('Otomatis dari box/bobbin; sesuaikan dengan timbangan.')
                                    ->live(onBlur: true),
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

        $merge = $state['merge'] ?? null;
        $berat = isset($state['berat_kg']) && is_numeric($state['berat_kg'])
            ? number_format((float) $state['berat_kg'], 2, ',', '.')
            : null;

        return trim($barang.($merge ? " / {$merge}" : '').($berat ? " — {$berat} kg" : ''));
    }
}
