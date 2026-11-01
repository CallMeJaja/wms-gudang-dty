<?php

namespace App\Filament\Resources\PengeluaranPoy\Schemas;

use App\Enums\JenisKeluar;
use App\Filament\Support\PengeluaranForm;
use App\Services\NomorDokumenService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                Section::make('Identitas Dokumen')
                    ->description('Pengeluaran bahan baku POY ke lantai produksi. Nomor transaksi dibuat otomatis.')
                    ->columns(3)
                    ->columnSpanFull()
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

                        Textarea::make('catatan')
                            ->label('Catatan Transaksi')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade.')
                    ->columnSpanFull()
                    ->schema([
                        PengeluaranForm::detailRepeater(JenisKeluar::PoyProduksi),
                    ]),
            ]);
    }
}
