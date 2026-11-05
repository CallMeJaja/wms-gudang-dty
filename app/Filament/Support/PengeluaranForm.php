<?php

namespace App\Filament\Support;

use App\Enums\JenisKeluar;
use App\Models\Barang;
use App\Models\Grade;
use App\Services\PengeluaranService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

/**
 * Komponen form bersama tiga resource pengeluaran (POY, Surat Jalan, Scrap).
 *
 * Baris detail sengaja tidak menampilkan `satuan_kemasan_id`: pengeluaran
 * hanya memakai Kemasan/Bobbin sebagai atribut fisik, bukan sumber saldo KG.
 */
final class PengeluaranForm
{
    /**
     * Opsi shift dari config/wms.php.
     *
     * @return array<int, string>
     */
    public static function opsiShift(): array
    {
        return WmsForm::opsiShift();
    }

    public static function totalNetto(mixed $detail): float
    {
        if (! is_array($detail)) {
            return 0.0;
        }

        return round(array_sum(array_map(
            fn ($baris): float => is_array($baris) ? (float) ($baris['berat_netto_kg'] ?? 0) : 0.0,
            $detail,
        )), 2);
    }

    public static function totalGross(mixed $detail): float
    {
        if (! is_array($detail)) {
            return 0.0;
        }

        return round(array_sum(array_map(
            fn ($baris): float => is_array($baris) ? (float) ($baris['berat_gross_kg'] ?? 0) : 0.0,
            $detail,
        )), 2);
    }

    public static function detailRepeater(JenisKeluar $jenis): Repeater
    {
        $kodeKategori = PengeluaranService::kodeKategori($jenis);

        $komponen = [
            self::pilihBarang($kodeKategori),

            TextInput::make('merge')
                ->label('Merge')
                ->required()
                ->maxLength(50)
                ->placeholder('4P1044'),

            self::pilihGrade(),
        ];

        if ($jenis === JenisKeluar::PoyProduksi) {
            $komponen[] = self::inputKemasanPoy();
            $komponen[] = self::inputBobbinPoy();
        } elseif ($jenis === JenisKeluar::DtySuratJalan) {
            $komponen[] = self::inputBobbinDty();
        } else {
            $komponen[] = TextInput::make('jumlah_kemasan')
                ->label('Kemasan')
                ->numeric()
                ->minValue(0);

            $komponen[] = TextInput::make('jumlah_bobbin')
                ->label('Bobbin')
                ->numeric()
                ->integer()
                ->minValue(0);
        }

        $komponen[] = self::inputNetto();
        $komponen[] = self::inputGross();

        if ($jenis !== JenisKeluar::DtySuratJalan) {
            $komponen[] = TextInput::make('keterangan')
                ->label('Keterangan')
                ->maxLength(255);
        }

        return Repeater::make('detail')
            ->relationship()
            ->label('')
            ->addActionLabel('Tambah baris')
            ->defaultItems(1)
            ->minItems(1)
            ->collapsible()
            ->reorderable(false)
            ->generateUuidUsing(false)
            ->columns(['default' => 1, 'md' => 3])
            ->itemLabel(fn (array $state): ?string => self::labelBaris($state))
            ->mutateRelationshipDataBeforeCreateUsing(
                fn (array $data): array => PengeluaranDetailMapper::normalisasi($data)
            )
            ->mutateRelationshipDataBeforeSaveUsing(
                fn (array $data): array => PengeluaranDetailMapper::normalisasi($data)
            )
            ->schema($komponen);
    }

    private static function inputKemasanPoy(): TextInput
    {
        return TextInput::make('jumlah_kemasan')
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
                $set('berat_netto_kg', round($box * (float) config('wms.satuan.poy_pallet_kg'), 2));
            });
    }

    private static function inputBobbinPoy(): TextInput
    {
        return TextInput::make('jumlah_bobbin')
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
                $set('berat_netto_kg', round($bobbin * (float) config('wms.satuan.poy_bobbin_kg'), 2));
            });
    }

    private static function inputBobbinDty(): TextInput
    {
        return TextInput::make('jumlah_bobbin')
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

                $grossPerBobbin = (float) config('wms.satuan.dty_box_gross_kg')
                    / max(1, (float) config('wms.satuan.dty_box_bobbin'));

                $set('berat_netto_kg', round($bobbin * (float) config('wms.satuan.dty_bobbin_kg'), 2));
                $set('berat_gross_kg', round($bobbin * $grossPerBobbin, 2));
            });
    }

    private static function inputNetto(): TextInput
    {
        return TextInput::make('berat_netto_kg')
            ->label('Netto (KG)')
            ->numeric()
            ->required()
            ->minValue(0.01)
            ->step('0.01')
            ->suffix('kg')
            ->helperText('Otomatis dari box/bobbin; sesuaikan dengan timbangan.')
            ->live(onBlur: true);
    }

    private static function inputGross(): TextInput
    {
        return TextInput::make('berat_gross_kg')
            ->label('Gross (KG)')
            ->numeric()
            ->minValue(0.01)
            ->step('0.01')
            ->suffix('kg')
            ->helperText('Otomatis dari bobbin; sesuaikan dengan timbangan.')
            ->live(onBlur: true)
            ->rules([
                fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                    if ($value === null || $value === '' || ! is_numeric($value)) {
                        return;
                    }

                    $netto = $get('berat_netto_kg');

                    if (is_numeric($netto) && (float) $value < (float) $netto) {
                        $fail('Berat Gross (Kg) tidak boleh lebih kecil dari Berat Netto (Kg).');
                    }
                },
            ]);
    }

    private static function pilihBarang(string $kodeKategori): Select
    {
        return Select::make('barang_id')
            ->label('Barang')
            ->relationship(
                name: 'barang',
                titleAttribute: 'nama_barang',
                modifyQueryUsing: fn (Builder $query) => $query
                    ->where('is_active', true)
                    ->whereHas('kategori', fn (Builder $q) => $q->where('kode', $kodeKategori))
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
            ->afterStateUpdated(fn (Set $set) => $set('grade_id', null))
            ->rules([
                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($kodeKategori): void {
                    $barang = is_numeric($value) ? Barang::query()->with('kategori')->find($value) : null;

                    if ($barang === null || $barang->kategori?->kode !== $kodeKategori) {
                        $fail("Barang yang dipilih harus berkategori {$kodeKategori}.");
                    }
                },
            ]);
    }

    private static function pilihGrade(): Select
    {
        return Select::make('grade_id')
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
            ->rules([
                fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                    $barang = is_numeric($get('barang_id'))
                        ? Barang::query()->find($get('barang_id'))
                        : null;
                    $grade = is_numeric($value) ? Grade::query()->find($value) : null;

                    if ($barang !== null && $grade !== null
                        && (int) $grade->kategori_id !== (int) $barang->kategori_id) {
                        $fail('Grade yang dipilih bukan milik kategori barang.');
                    }
                },
            ]);
    }

    /**
     * Judul ringkas tiap baris agar mudah dibaca saat repeater dilipat.
     *
     * @param  array<string, mixed>  $state
     */
    private static function labelBaris(array $state): ?string
    {
        $barang = isset($state['barang_id']) && is_numeric($state['barang_id'])
            ? Barang::query()->find($state['barang_id'])?->kode_barang
            : null;

        if ($barang === null) {
            return null;
        }

        $merge = $state['merge'] ?? null;
        $netto = isset($state['berat_netto_kg']) && is_numeric($state['berat_netto_kg'])
            ? number_format((float) $state['berat_netto_kg'], 2, ',', '.')
            : null;

        return trim($barang.($merge ? " / {$merge}" : '').($netto ? " — {$netto} kg" : ''));
    }
}
