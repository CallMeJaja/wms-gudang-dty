<?php

namespace App\Filament\Resources\PenerimaanDty\Schemas;

use App\Enums\JenisMasuk;
use App\Filament\Support\PenerimaanDetailMapper;
use App\Filament\Support\PenerimaanForm;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\TransaksiMasukDetail;
use App\Services\KonversiSatuanService;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
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
 * Form Penerimaan DTY hasil produksi.
 *
 * Box -> bobbin/netto/gross di-auto-fill dari config/wms.php, tetapi tetap
 * dapat dioverride manual. Gross disimpan sebagai penanda [GROSS=..] pada
 * `keterangan` karena skema detail masuk tidak punya kolom gross (lihat
 * TransaksiMasukDetail).
 */
class PenerimaanDtyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identitas Dokumen')
                            ->description('Penerimaan DTY hasil produksi. Shift, operator, dan merge POY asal wajib untuk rendemen.')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_transaksi')
                                    ->label('Nomor Transaksi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(fn () => app(NomorDokumenService::class)->peek('TRX_MASUK'))
                                    ->helperText('Digenerate otomatis saat disimpan.'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Produksi')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),

                                Select::make('shift')
                                    ->label('Shift')
                                    ->options(PenerimaanForm::opsiShift())
                                    ->required()
                                    ->native(false),

                                TextInput::make('operator_nama')
                                    ->label('Nama Operator')
                                    ->required()
                                    ->maxLength(100)
                                    ->helperText('Akun dipakai bersama per shift.'),

                                TextInput::make('sumber_internal')
                                    ->label('Sumber Internal')
                                    ->maxLength(100)
                                    ->default(fn () => config('wms.inbound.sumber_internal_dty'))
                                    ->helperText('Area asal produksi (default AREA PRODUKSI).'),

                                TextInput::make('merge_poy_asal')
                                    ->label('Merge POY Asal')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('4P1044')
                                    ->helperText('Wajib untuk laporan rendemen POY vs DTY.'),
                            ]),
                        Section::make('Ringkasan')
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('jenis_dokumen')
                                    ->label('Jenis Dokumen')
                                    ->content(JenisMasuk::DtyProduksi->label()),
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
                    ->description('Satu baris = satu kombinasi barang + merge + grade. Isi Box untuk mengisi bobbin/netto/gross otomatis.')
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
                            ->columns(['default' => 1, 'md' => 2, 'lg' => 4])
                            ->itemLabel(fn (array $state): ?string => self::labelBaris($state))
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $data['berat_gross_kg'] = TransaksiMasukDetail::grossDariKeterangan($data['keterangan'] ?? null);
                                $data['keterangan'] = TransaksiMasukDetail::batchDariKeterangan($data['keterangan'] ?? null) ?? '';

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(
                                fn (array $data): array => PenerimaanDetailMapper::dty($data)
                            )
                            ->mutateRelationshipDataBeforeSaveUsing(
                                fn (array $data): array => PenerimaanDetailMapper::dty($data)
                            )
                            ->schema([
                                Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship(
                                        name: 'barang',
                                        titleAttribute: 'nama_barang',
                                        modifyQueryUsing: fn (Builder $query) => $query
                                            ->where('is_active', true)
                                            ->whereHas('kategori', fn (Builder $q) => $q->where('kode', KategoriBarang::DTY))
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
                                    ->placeholder('4D9160'),

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
                                    ->required()
                                    ->live(),

                                TextInput::make('jumlah_kemasan')
                                    ->label('Box')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, $state): void {
                                        $box = (float) $state;

                                        if ($box <= 0) {
                                            return;
                                        }

                                        $isi = app(KonversiSatuanService::class)->autoFillDty($box);
                                        $set('jumlah_bobbin', $isi['bobbin']);
                                        $set('berat_kg', $isi['netto_kg']);
                                        $set('berat_gross_kg', $isi['gross_kg']);
                                    }),

                                TextInput::make('jumlah_bobbin')
                                    ->label('Bobbin')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0),

                                TextInput::make('berat_kg')
                                    ->label('Netto (KG)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step('0.01')
                                    ->suffix('kg')
                                    ->live(onBlur: true),

                                TextInput::make('berat_gross_kg')
                                    ->label('Gross (KG)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step('0.01')
                                    ->suffix('kg')
                                    ->live(onBlur: true)
                                    ->hint(fn (Get $get): ?string => self::hintGross($get)),

                                TextInput::make('keterangan')
                                    ->label('Batch')
                                    ->maxLength(200)
                                    ->placeholder('batch / catatan'),
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
     * Petunjuk toleransi gross per box berdasarkan grade (tidak memblokir).
     */
    private static function hintGross(Get $get): ?string
    {
        $box = (float) $get('jumlah_kemasan');
        $gross = (float) $get('berat_gross_kg');
        $kode = Grade::query()->find($get('grade_id'))?->kode;

        if ($box <= 0 || $gross <= 0 || $kode === null) {
            return null;
        }

        $perBox = PenerimaanService::grossPerBox($gross, $box);
        $rentang = PenerimaanService::rentangGrossGrade($kode);

        if ($perBox === null || $rentang === null) {
            return null;
        }

        $diLuar = PenerimaanService::grossDiLuarToleransi($perBox, $kode);
        $status = $diLuar ? 'di luar' : 'sesuai';

        return sprintf(
            '%s kg/box — %s toleransi grade %s (%s–%s kg)',
            number_format($perBox, 2, ',', '.'),
            $status,
            $kode,
            number_format($rentang['min'], 2, ',', '.'),
            number_format($rentang['max'], 2, ',', '.'),
        );
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
        $box = isset($state['jumlah_kemasan']) && is_numeric($state['jumlah_kemasan'])
            ? rtrim(rtrim(number_format((float) $state['jumlah_kemasan'], 2, ',', '.'), '0'), ',')
            : null;

        return trim($barang.($merge ? " / {$merge}" : '').($box ? " — {$box} box" : ''));
    }
}
