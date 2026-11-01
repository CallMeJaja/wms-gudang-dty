<?php

namespace App\Filament\Resources\PengeluaranPoy\Schemas;

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
use Filament\Support\Icons\Heroicon;

/**
 * Form pengeluaran POY ke lantai produksi (PRD §4.3).
 *
 * Detail tidak menampilkan satuan kemasan karena form legacy pengeluaran
 * hanya mencatat Kemasan/Bobbin sebagai atribut fisik.
 */
class PengeluaranPoyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identitas Dokumen')
                            ->description('Pengeluaran bahan baku POY ke lantai produksi. Nomor transaksi dibuat otomatis.')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_transaksi')
                                    ->label('Nomor Transaksi')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(fn () => app(NomorDokumenService::class)->peek('TRX_KELUAR'))
                                    ->helperText('Digenerate otomatis saat disimpan.'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Keluar')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),

                                Select::make('shift')
                                    ->label('Shift')
                                    ->options(PengeluaranForm::opsiShift())
                                    ->required()
                                    ->native(false),

                                TextInput::make('operator_nama')
                                    ->label('Nama Operator')
                                    ->required()
                                    ->maxLength(100)
                                    ->helperText('Akun dipakai bersama per shift.'),
                            ]),

                        Section::make('Ringkasan')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('jenis_dokumen')
                                    ->label('Jenis Dokumen')
                                    ->content('Pengeluaran POY'),
                                Placeholder::make('status_dokumen')
                                    ->label('Status')
                                    ->content('Draf'),
                                Placeholder::make('total_netto')
                                    ->label('Total Netto')
                                    ->content(fn (Get $get): string => number_format(PengeluaranForm::totalNetto($get('detail')), 2, ',', '.').' kg'),
                                Placeholder::make('alur_stok')
                                    ->label('Alur Stok')
                                    ->content('Stok aktif berkurang setelah Posting.'),
                            ]),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade.')
                    ->icon(Heroicon::OutlinedTableCells)
                    ->columnSpanFull()
                    ->schema([
                        PengeluaranForm::detailRepeater(JenisKeluar::PoyProduksi),
                    ]),

                Section::make('Catatan')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
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
