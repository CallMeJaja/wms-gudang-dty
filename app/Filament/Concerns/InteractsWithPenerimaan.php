<?php

namespace App\Filament\Concerns;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\TransaksiMasuk;
use App\Services\PenerimaanService;
use App\Services\VoidTransaksiService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Perilaku bersama tiga resource penerimaan (POY, Gate Pass, DTY).
 *
 * - Query & route binding selalu dibatasi ke satu `jenis_masuk`, sehingga
 *   record jenis lain tidak bisa dibuka lewat URL resource yang salah.
 * - Otorisasi memakai PenerimaanPolicy yang sudah ada, dengan argumen jenis.
 * - Aksi Posting/Issue/Void memanggil service bisnis yang sama seperti
 *   controller lama — tidak ada logika stok yang diduplikasi di UI.
 */
trait InteractsWithPenerimaan
{
    abstract public static function jenisMasuk(): JenisMasuk;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('jenis_masuk', static::jenisMasuk()->value);
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
        return Auth::user()?->can('update', [$record, static::jenisMasuk()]) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('delete', [$record, static::jenisMasuk()]) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()?->can('view', [$record, static::jenisMasuk()]) ?? false;
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
        $tambahan = match (static::jenisMasuk()) {
            JenisMasuk::PendukungGatepass => ['nomor_gate_pass'],
            JenisMasuk::DtyProduksi => ['merge_poy_asal'],
            JenisMasuk::PoyIpci => ['operator_nama'],
            default => [],
        };

        return ['nomor_transaksi', ...$tambahan];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
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
            ->iconButton()
            ->tooltip('Lihat ringkasan')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (TransaksiMasuk $record): string => (string) $record->nomor_transaksi)
            ->modalWidth('5xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(fn (TransaksiMasuk $record) => view('filament.penerimaan.ringkasan', [
                'transaksi' => $record->loadMissing([
                    'detail.barang.kategori',
                    'detail.grade',
                    'detail.satuanKemasan',
                    'partner',
                    'pembuat',
                    'pemvoid',
                    'alokasiBuffer',
                ]),
            ]))
            ->visible(fn (TransaksiMasuk $record): bool => static::canView($record));
    }

    public static function aksiUbah(): EditAction
    {
        return EditAction::make()
            ->iconButton()
            ->tooltip('Ubah draf')
            ->visible(fn (TransaksiMasuk $record): bool => static::canEdit($record));
    }

    public static function aksiPosting(): Action
    {
        $jenis = static::jenisMasuk();
        $label = $jenis->isDokumenResmi() ? 'Terbitkan' : 'Posting';

        return Action::make('posting')
            ->label($label)
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading("{$label} transaksi?")
            ->modalDescription($jenis->isDokumenResmi()
                ? 'Dokumen akan diterbitkan dan stok bertambah. Setelah terbit, dokumen tidak dapat diubah.'
                : 'Stok akan bertambah dan dokumen tidak dapat diubah lagi setelah diposting.')
            ->visible(fn (TransaksiMasuk $record): bool => $record->status === StatusDokumen::Draft
                && (Auth::user()?->can($jenis->isDokumenResmi() ? 'issue' : 'posting', [$record, $jenis]) ?? false))
            ->action(function (TransaksiMasuk $record): void {
                try {
                    app(PenerimaanService::class)->posting($record, Auth::id());

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
        $jenis = static::jenisMasuk();

        return Action::make('void')
            ->label('Void')
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
            ->visible(fn (TransaksiMasuk $record): bool => Auth::user()?->can('void', [$record, $jenis]) ?? false)
            ->action(function (TransaksiMasuk $record, array $data): void {
                try {
                    app(VoidTransaksiService::class)->voidTransaksiMasuk(
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
            ->iconButton()
            ->tooltip('Hapus draf')
            ->visible(fn (TransaksiMasuk $record): bool => static::canDelete($record));
    }

    public static function aksiCetak(): Action
    {
        return Action::make('cetak')
            ->label('Cetak')
            ->iconButton()
            ->tooltip('Cetak Gate Pass')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn (TransaksiMasuk $record): string => static::getUrl('cetak', ['record' => $record]))
            ->openUrlInNewTab()
            ->visible(fn (TransaksiMasuk $record): bool => $record->status !== StatusDokumen::Draft
                && static::canView($record));
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    public static function aksiPenerimaan(): array
    {
        $aksi = [
            static::aksiPosting(),
            static::aksiVoid(),
        ];

        if (static::jenisMasuk() === JenisMasuk::PendukungGatepass) {
            $aksi[] = static::aksiCetak();
        }

        $aksi[] = ActionGroup::make([
            static::aksiLihat(),
            static::aksiUbah(),
            static::aksiHapus(),
        ])
            ->label('Aksi')
            ->icon('heroicon-o-ellipsis-vertical')
            ->iconButton();

        return $aksi;
    }

    /**
     * Opsi filter status sesuai lifecycle jenis penerimaan.
     *
     * @return array<string, string>
     */
    public static function opsiStatus(): array
    {
        $status = static::jenisMasuk()->isDokumenResmi()
            ? [StatusDokumen::Draft, StatusDokumen::Issued, StatusDokumen::Cancelled, StatusDokumen::Void]
            : [StatusDokumen::Draft, StatusDokumen::Posted, StatusDokumen::Void];

        return collect($status)
            ->mapWithKeys(fn (StatusDokumen $s): array => [$s->value => $s->label()])
            ->all();
    }
}
