<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlertStokWidget;
use App\Filament\Widgets\GrafikStokWidget;
use App\Filament\Widgets\RingkasanOperasionalWidget;
use App\Services\AlokasiBufferService;
use App\Services\RilisBufferService;
use App\Services\ShiftService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 0;

    public static function getNavigationLabel(): string|Htmlable
    {
        return 'Beranda';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Beranda';
    }

    public function getSubheading(): ?string
    {
        return sprintf(
            '%s · Shift berjalan: %s',
            now()->locale('id')->translatedFormat('l, d F Y'),
            app(ShiftService::class)->label(app(ShiftService::class)->current()),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rilisBuffer')
                ->label('Rilis Buffer Minggu')
                ->icon('heroicon-o-arrow-up-on-square')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Rilis seluruh buffer Minggu ke stok aktif?')
                ->visible(function (): bool {
                    $user = Auth::user();

                    if ($user === null || ! $user->canWrite()) {
                        return false;
                    }

                    if (! app(RilisBufferService::class)->bolehRilisOtomatis(now())) {
                        return false;
                    }

                    return app(AlokasiBufferService::class)->statusBuffer()->bufferKg > 0;
                })
                ->action(function (): void {
                    $hasil = app(RilisBufferService::class)->rilis(now(), Auth::id());

                    Notification::make()
                        ->title('Buffer Minggu dirilis')
                        ->body(sprintf(
                            '%s kg buffer dirilis ke stok aktif (%d baris).',
                            number_format($hasil['dirilis_kg'], 2, ',', '.'),
                            $hasil['baris'],
                        ))
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            RingkasanOperasionalWidget::class,
            GrafikStokWidget::class,
            AlertStokWidget::class,
        ];
    }
}
