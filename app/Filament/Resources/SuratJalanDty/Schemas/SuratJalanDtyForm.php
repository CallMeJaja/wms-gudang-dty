<?php

namespace App\Filament\Resources\SuratJalanDty\Schemas;

use App\Enums\JenisKeluar;
use App\Filament\Support\PengeluaranForm;
use App\Services\NomorDokumenService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Form Surat Jalan DTY.
 *
 * Nomor Surat Jalan boleh diisi manual sesuai dokumen fisik; bila kosong,
 * PengeluaranService::issue() akan generate otomatis saat Terbitkan.
 */
class SuratJalanDtyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identitas Dokumen')
                            ->description('Pengiriman DTY ke pelanggan. Stok berkurang saat dokumen diterbitkan.')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_transaksi')
                                    ->label('Nomor Transaksi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(fn () => app(NomorDokumenService::class)->peek('TRX_KELUAR'))
                                    ->helperText('Digenerate otomatis saat disimpan.'),

                                TextInput::make('nomor_surat_jalan')
                                    ->label('Nomor Surat Jalan')
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder(fn () => app(NomorDokumenService::class)->peek('SURAT_JALAN_DTY'))
                                    ->helperText('Kosongkan untuk generate otomatis saat Terbitkan.')
                                    ->dehydrateStateUsing(
                                        fn (?string $state): ?string => filled($state) ? trim($state) : null
                                    ),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Kirim')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),

                                Select::make('partner_id')
                                    ->label('Mitra / Pelanggan')
                                    ->relationship(
                                        name: 'partner',
                                        titleAttribute: 'nama',
                                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('nama'),
                                    )
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('no_kendaraan')
                                    ->label('No. Kendaraan')
                                    ->required()
                                    ->maxLength(30)
                                    ->placeholder('B 1234 CD'),

                                TextInput::make('nama_sopir')
                                    ->label('Nama Sopir')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('pic_pengirim')
                                    ->label('PIC Pengirim')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('pic_mengetahui')
                                    ->label('PIC Mengetahui')
                                    ->required()
                                    ->maxLength(100),
                            ]),

                        Section::make('Ringkasan')
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('jenis_dokumen')
                                    ->label('Jenis Dokumen')
                                    ->content('Surat Jalan DTY'),
                                Placeholder::make('status_dokumen')
                                    ->label('Status')
                                    ->content('Draf'),
                                Placeholder::make('total_netto')
                                    ->label('Total Netto')
                                    ->content(fn (Get $get): string => number_format(PengeluaranForm::totalNetto($get('detail')), 2, ',', '.').' kg'),
                                Placeholder::make('total_gross')
                                    ->label('Total Gross')
                                    ->content(fn (Get $get): string => number_format(PengeluaranForm::totalGross($get('detail')), 2, ',', '.').' kg'),
                                Placeholder::make('alur_stok')
                                    ->label('Alur Stok')
                                    ->content('Stok DTY berkurang setelah Terbitkan.'),
                            ]),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade.')
                    ->columnSpanFull()
                    ->schema([
                        PengeluaranForm::detailRepeater(JenisKeluar::DtySuratJalan)
                            ->addActionAlignment(Alignment::End),
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
}
