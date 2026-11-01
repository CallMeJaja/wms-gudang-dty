<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardData;
use App\Services\StatusBufferMinggu;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanOperasionalWidget extends StatsOverviewWidget
{
    protected static ?bool $isLazyLoaded = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stok = DashboardData::stokAktifPoy();
        $buffer = DashboardData::statusBuffer();
        $draf = DashboardData::drafMenungguPosting();

        return [
            Stat::make(
                'Stok Aktif POY',
                sprintf('%s / %d pallet', number_format($stok['pallet'], 2, ',', '.'), $stok['minPallet'])
            )
                ->description(sprintf(
                    '%s kg — %s',
                    number_format($stok['kg'], 2, ',', '.'),
                    $stok['aman'] ? 'Aman' : 'Risiko stop produksi'
                ))
                ->descriptionIcon($stok['aman'] ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                ->color($stok['aman'] ? 'success' : 'danger'),

            Stat::make(
                'Buffer Minggu',
                sprintf('%s / %d pallet', number_format($buffer->bufferPallet, 2, ',', '.'), $buffer->targetPallet)
            )
                ->description($buffer->label.' — '.$buffer->pesan)
                ->descriptionIcon(match ($buffer->level) {
                    StatusBufferMinggu::SIAP => 'heroicon-o-check-circle',
                    StatusBufferMinggu::TERLAMBAT => 'heroicon-o-exclamation-triangle',
                    default => 'heroicon-o-clock',
                })
                ->color(match ($buffer->level) {
                    StatusBufferMinggu::SIAP => 'success',
                    StatusBufferMinggu::TERLAMBAT => 'danger',
                    default => 'warning',
                }),

            Stat::make('Draf Menunggu Posting', (string) $draf['total'])
                ->description($draf['total'] > 0
                    ? sprintf('Penerimaan %d · Pengeluaran %d', $draf['penerimaan'], $draf['pengeluaran'])
                    : 'Tidak ada draf')
                ->descriptionIcon('heroicon-o-document-text')
                ->color($draf['total'] > 0 ? 'warning' : 'success'),
        ];
    }
}
