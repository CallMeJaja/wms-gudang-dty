<?php

namespace App\Filament\Resources\PengeluaranScrap\Schemas;

use App\Enums\JenisKeluar;
use App\Filament\Support\PengeluaranForm;
use App\Models\TransaksiKeluar;
use App\Services\NomorDokumenService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

/**
 * Form pengeluaran Scrap / Reject DTY.
 *
 * Catatan wajib karena menjadi satu-satunya jejak tujuan/penerima scrap.
 */
class PengeluaranScrapForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identitas Dokumen')
                            ->description('Pengeluaran scrap/reject DTY. Catatan wajib menyebut tujuan atau penerima.')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_transaksi')
                                    ->label('Nomor Transaksi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(fn () => app(NomorDokumenService::class)->peekUnik(
                                        'TRX_KELUAR',
                                        fn (string $n): bool => TransaksiKeluar::query()->where('nomor_transaksi', $n)->exists(),
                                    ))
                                    ->helperText('Digenerate otomatis saat disimpan.'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Keluar')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                            ]),

                        Section::make('Ringkasan')
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('jenis_dokumen')
                                    ->label('Jenis Dokumen')
                                    ->content('Scrap / Reject'),
                                Placeholder::make('status_dokumen')
                                    ->label('Status')
                                    ->content('Draf'),
                                Placeholder::make('total_netto')
                                    ->label('Total Netto')
                                    ->content(fn (Get $get): string => number_format(PengeluaranForm::totalNetto($get('detail')), 2, ',', '.').' kg'),
                                Placeholder::make('alur_stok')
                                    ->label('Alur Stok')
                                    ->content('Stok DTY berkurang setelah Posting. Catatan wajib.'),
                            ]),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade.')
                    ->columnSpanFull()
                    ->schema([
                        PengeluaranForm::detailRepeater(JenisKeluar::DtyScrapReject)
                            ->addActionAlignment(Alignment::End),
                    ]),

                Section::make('Catatan')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan (Tujuan/Penerima Scrap)')
                            ->required()
                            ->minLength(3)
                            ->maxLength(2000)
                            ->rows(3),
                    ]),
            ]);
    }
}
