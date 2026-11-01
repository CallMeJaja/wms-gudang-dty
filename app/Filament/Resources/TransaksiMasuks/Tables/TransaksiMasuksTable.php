<?php

namespace App\Filament\Resources\TransaksiMasuks\Tables;

use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\TransaksiMasuk;
use App\Services\PenerimaanService;
use App\Services\VoidTransaksiService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransaksiMasuksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->placeholder('(draft)')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jenis_masuk')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'POY_IPCI' => 'warning',
                        'DTY_PRODUKSI' => 'success',
                        'PENDUKUNG_GATEPASS' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('partner.nama')
                    ->label('Supplier')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('total_berat_kg')
                    ->label('Total KG')
                    ->alignEnd()
                    // Catatan: parameter $record SENGAJA tanpa type-hint.
                    // Filament hanya inject record asli bila nama parameternya
                    // `record`; bila di-type-hint, ia jatuh ke app()->make()
                    // dan menghasilkan model kosong (nilai selalu 0).
                    ->state(function ($record): string {
                        return number_format((float) $record->detail->sum('berat_kg'), 2, ',', '.');
                    }),

                TextColumn::make('detail_count')
                    ->label('Baris')
                    ->state(fn (TransaksiMasuk $record) => $record->detail->count())
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                    ->color(fn ($state) => match ($state) {
                        StatusDokumen::Draft => 'gray',
                        StatusDokumen::Posted => 'success',
                        StatusDokumen::Issued => 'info',
                        StatusDokumen::Void => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('pembuat.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(StatusDokumen::cases())
                        ->mapWithKeys(fn (StatusDokumen $s) => [$s->value => $s->label()])
                        ->all()),

                SelectFilter::make('jenis_masuk')
                    ->label('Jenis')
                    ->options(collect(\App\Enums\JenisMasuk::cases())
                        ->mapWithKeys(fn (\App\Enums\JenisMasuk $j) => [$j->value => $j->label()])
                        ->all()),

                \Filament\Tables\Filters\Filter::make('tanggal')
                    ->label('Rentang Tanggal')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('dari')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('sampai')->label('Sampai'),
                    ])
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn ($q, $d) => $q->whereDate('tanggal', '>=', $d))
                        ->when($data['sampai'] ?? null, fn ($q, $d) => $q->whereDate('tanggal', '<=', $d))),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (TransaksiMasuk $record) => $record->status === StatusDokumen::Draft),

                // ---- POSTING: memanggil PenerimaanService yang sudah ada ----
                Action::make('posting')
                    ->label('Posting')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Posting transaksi penerimaan?')
                    ->modalDescription('Stok akan bertambah dan dokumen tidak bisa diedit lagi setelah posting.')
                    ->visible(fn (TransaksiMasuk $record) => $record->status === StatusDokumen::Draft
                        && (Auth::user()?->canWrite() ?? false))
                    ->action(function (TransaksiMasuk $record) {
                        try {
                            app(PenerimaanService::class)->posting($record, Auth::id());

                            Notification::make()
                                ->title('Transaksi berhasil diposting')
                                ->body("Stok telah diperbarui untuk {$record->nomor_transaksi}.")
                                ->success()
                                ->send();
                        } catch (AturanBisnisException $e) {
                            Notification::make()
                                ->title('Posting gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ---- VOID: hanya Admin (PRD §2) ----
                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Void transaksi?')
                    ->modalDescription('Stok akan dikembalikan ke posisi sebelum transaksi. Tindakan ini tercatat di log.')
                    ->visible(fn (TransaksiMasuk $record) => $record->status !== StatusDokumen::Void
                        && (Auth::user()?->canManage() ?? false))
                    ->schema([
                        Textarea::make('alasan_void')
                            ->label('Alasan Void')
                            ->required()
                            ->minLength(5)
                            ->rows(3),
                    ])
                    ->action(function (TransaksiMasuk $record, array $data) {
                        try {
                            app(VoidTransaksiService::class)->voidTransaksiMasuk($record, $data['alasan_void'], Auth::id());

                            Notification::make()
                                ->title('Transaksi di-void')
                                ->success()
                                ->send();
                        } catch (AturanBisnisException $e) {
                            Notification::make()
                                ->title('Void gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                DeleteAction::make()
                    ->visible(fn (TransaksiMasuk $record) => $record->status === StatusDokumen::Draft),
            ])
            ->emptyStateHeading('Belum ada penerimaan')
            ->emptyStateDescription('Buat transaksi penerimaan untuk menambah stok gudang.')
            ->emptyStateIcon('heroicon-o-arrow-down-tray');
    }
}
