<?php

namespace App\Filament\Resources\PengeluaranScrap\Schemas;

use App\Enums\JenisKeluar;
use App\Filament\Support\PengeluaranForm;
use App\Services\NomorDokumenService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form pengeluaran Scrap / Reject DTY (PRD §7.3 no.12).
 *
 * Catatan wajib karena menjadi satu-satunya jejak tujuan/penerima scrap.
 */
class PengeluaranScrapForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Dokumen')
                    ->description('Pengeluaran scrap/reject DTY. Catatan wajib menyebut tujuan atau penerima.')
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

                        Textarea::make('catatan')
                            ->label('Catatan (Tujuan/Penerima Scrap)')
                            ->required()
                            ->minLength(3)
                            ->maxLength(2000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Rincian Barang')
                    ->description('Satu baris = satu kombinasi barang + merge + grade.')
                    ->columnSpanFull()
                    ->schema([
                        PengeluaranForm::detailRepeater(JenisKeluar::DtyScrapReject),
                    ]),
            ]);
    }
}
