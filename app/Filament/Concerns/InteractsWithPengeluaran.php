<?php

namespace App\Filament\Concerns;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\TransaksiKeluar;
use App\Services\PengeluaranService;
use App\Services\VoidTransaksiService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Perilaku bersama tiga resource pengeluaran (POY, Surat Jalan, Scrap).
 *
 * - Query & route binding selalu dibatasi ke satu `jenis_keluar`, sehingga
 *   record jenis lain tidak bisa dibuka lewat URL resource yang salah.
 * - Otorisasi memakai PengeluaranPolicy yang sudah ada, dengan argumen jenis.
 * - Aksi Posting/Issue/Void memanggil service bisnis yang sama dengan
 *   alur backend — tidak ada logika stok yang diduplikasi di UI.
 */
trait InteractsWithPengeluaran
{
    abstract public static function jenisKeluar(): JenisKeluar;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('jenis_keluar', static::jenisKeluar()->value);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('viewAny', static::getModel()) ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create', static::getModel()) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('update', [$record, static::jenisKeluar()]) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('delete', [$record, static::jenisKeluar()]) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('view', [$record, static::jenisKeluar()]) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', StatusDokumen::Draft->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        $tambahan = match (static::jenisKeluar()) {
            JenisKeluar::PoyProduksi => ['operator_nama'],
            JenisKeluar::DtySuratJalan => ['nomor_surat_jalan', 'nama_sopir'],
            default => [],
        };

        return ['nomor_transaksi', ...$tambahan];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        if (static::jenisKeluar() === JenisKeluar::DtySuratJalan && filled($record->nomor_surat_jalan)) {
            return (string) $record->nomor_surat_jalan;
        }

        return (string) $record->nomor_transaksi;
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tanggal' => $record->tanggal?->format('d M Y') ?? '—',
            'Status' => $record->status?->label() ?? '—',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index', ['tableSearch' => $record->nomor_transaksi]);
    }

    public static function aksiLihat(): Action
    {
        return Action::make('lihat')
            ->label('Lihat')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (TransaksiKeluar $record): string => (string) $record->nomor_transaksi)
            ->modalWidth('5xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(fn (TransaksiKeluar $record) => view('filament.pengeluaran.ringkasan', [
                'transaksi' => $record->loadMissing([
                    'detail.barang.kategori',
                    'detail.grade',
                    'detail.satuanKemasan',
                    'partner',
                    'pembuat',
                    'pemvoid',
                ]),
            ]))
            ->visible(fn (TransaksiKeluar $record): bool => static::canView($record));
    }

    public static function aksiUbah(): EditAction
    {
        return EditAction::make()
            ->visible(fn (TransaksiKeluar $record): bool => static::canEdit($record));
    }

    public static function aksiPosting(): Action
    {
        $jenis = static::jenisKeluar();
        $label = $jenis->isDokumenResmi() ? 'Terbitkan' : 'Posting';
        $izin = $jenis->isDokumenResmi() ? 'issue' : 'posting';

        return Action::make('posting')
            ->label($label)
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading("{$label} transaksi?")
            ->modalDescription($jenis->isDokumenResmi()
                ? 'Dokumen akan diterbitkan dan stok berkurang. Setelah terbit, dokumen tidak dapat diubah.'
                : 'Stok akan berkurang dan dokumen tidak dapat diubah lagi setelah diposting.')
            ->visible(fn (TransaksiKeluar $record): bool => $record->status === StatusDokumen::Draft
                && (Auth::user()?->can($izin, [$record, $jenis]) ?? false))
            ->action(function (TransaksiKeluar $record) use ($jenis): void {
                try {
                    $service = app(PengeluaranService::class);

                    if ($jenis->isDokumenResmi()) {
                        $service->issue($record, Auth::id());
                    } else {
                        $service->posting($record, Auth::id());
                    }

                    Notification::make()
                        ->title('Transaksi berhasil diproses')
                        ->body("Stok telah diperbarui untuk {$record->nomor_transaksi}.")
                        ->success()
                        ->send();
                } catch (AturanBisnisException $e) {
                    Notification::make()
                        ->title('Proses gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function aksiVoid(): Action
    {
        $jenis = static::jenisKeluar();

        return Action::make('void')
            ->label('Void')
            ->iconButton()
            ->tooltip('Void transaksi')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Void transaksi?')
            ->modalDescription('Stok akan dikembalikan ke posisi sebelum transaksi. Tindakan ini tercatat di log.')
            ->schema([
                Textarea::make('alasan_void')
                    ->label('Alasan Void')
                    ->required()
                    ->minLength(VoidTransaksiService::MIN_ALASAN)
                    ->maxLength(1000)
                    ->rows(3),
            ])
            ->visible(fn (TransaksiKeluar $record): bool => Auth::user()?->can('void', [$record, $jenis]) ?? false)
            ->action(function (TransaksiKeluar $record, array $data): void {
                try {
                    app(VoidTransaksiService::class)->voidTransaksiKeluar(
                        $record,
                        (string) $data['alasan_void'],
                        (int) Auth::id(),
                    );

                    Notification::make()
                        ->title('Transaksi di-void')
                        ->body("Stok {$record->nomor_transaksi} telah dikembalikan.")
                        ->success()
                        ->send();
                } catch (AturanBisnisException $e) {
                    Notification::make()
                        ->title('Void gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function aksiHapus(): DeleteAction
    {
        return DeleteAction::make()
            ->visible(fn (TransaksiKeluar $record): bool => static::canDelete($record));
    }

    /**
     * Cetak hanya tersedia untuk Surat Jalan yang sudah diterbitkan.
     */
    public static function aksiCetak(): Action
    {
        return Action::make('cetak')
            ->label('Cetak')
            ->iconButton()
            ->tooltip('Cetak Surat Jalan')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(
                fn (TransaksiKeluar $record): string => static::getUrl('cetak', ['record' => $record]),
                shouldOpenInNewTab: true,
            )
            ->visible(fn (TransaksiKeluar $record): bool => $record->status === StatusDokumen::Issued
                && static::canView($record));
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    public static function aksiPengeluaran(): array
    {
        $aksi = [
            static::aksiPosting(),
            static::aksiVoid(),
        ];

        if (static::jenisKeluar() === JenisKeluar::DtySuratJalan) {
            $aksi[] = static::aksiCetak();
        }

        $aksi[] = ActionGroup::make([
            static::aksiLihat(),
            static::aksiUbah(),
            static::aksiHapus(),
        ])
            ->label('Aksi lainnya')
            ->icon('heroicon-o-ellipsis-vertical')
            ->iconButton();

        return $aksi;
    }

    /**
     * Opsi filter status sesuai lifecycle jenis pengeluaran.
     *
     * @return array<string, string>
     */
    public static function opsiStatus(): array
    {
        $status = static::jenisKeluar()->isDokumenResmi()
            ? [StatusDokumen::Draft, StatusDokumen::Issued, StatusDokumen::Cancelled, StatusDokumen::Void]
            : [StatusDokumen::Draft, StatusDokumen::Posted, StatusDokumen::Void];

        return collect($status)
            ->mapWithKeys(fn (StatusDokumen $s): array => [$s->value => $s->label()])
            ->all();
    }

    public static function filterTanggal(): Filter
    {
        return Filter::make('tanggal')
            ->label('Rentang Tanggal')
            ->schema([
                DatePicker::make('dari')->label('Dari')->native(false),
                DatePicker::make('sampai')->label('Sampai')->native(false),
            ])
            ->query(fn (Builder $query, array $data) => $query
                ->when($data['dari'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '>=', $d))
                ->when($data['sampai'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal', '<=', $d)));
    }
}
