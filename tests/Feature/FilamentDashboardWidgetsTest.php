<?php

namespace Tests\Feature;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Filament\Pages\Dashboard;
use App\Models\StokBarang;
use App\Models\User;
use App\Services\StokService;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji dashboard Filament (F4): widget, subheading, dan aksi rilis buffer.
 *
 * DB memakai SQLite :memory: dari phpunit.xml sehingga database dev
 * tidak tersentuh.
 */
class FilamentDashboardWidgetsTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function kunciTanggal(string $tanggal): Carbon
    {
        $zona = (string) config('app.timezone');
        $saat = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal.' 10:00:00', $zona);
        Carbon::setTestNow($saat);

        return $saat;
    }

    private function isiBuffer(float $bufferKg, float $aktifKg = 0.0, string $kode = 'POY-250-48', string $merge = 'M-1'): void
    {
        $stok = app(StokService::class);
        $barang = $this->barangPoy(true, $kode);

        if ($aktifKg > 0) {
            $stok->tambahStok($barang, $merge, $this->gradePoyAl->id, $aktifKg, PosStok::Aktif, JenisMutasi::Masuk, 'UJI-AKTIF');
        }

        if ($bufferKg > 0) {
            $stok->tambahStok($barang, $merge, $this->gradePoyAl->id, $bufferKg, PosStok::Buffer, JenisMutasi::Masuk, 'UJI-BUFFER');
        }
    }

    #[Test]
    public function semua_role_bisa_membuka_dashboard_dan_melihat_widget(): void
    {
        $this->kunciHariKerja();

        foreach ([$this->admin, $this->operator, $this->manajer] as $user) {
            $this->actingAs($user)
                ->get('/admin')
                ->assertOk()
                ->assertSee('Stok Aktif POY')
                ->assertSee('Buffer Minggu')
                ->assertSee('Stok Harian 30 Hari')
                ->assertSee('Ringkasan per Kategori')
                ->assertSee('Alert');
        }
    }

    #[Test]
    public function admin_dan_operator_bisa_rilis_buffer_hari_minggu(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu

        foreach ([$this->admin, $this->operator] as $user) {
            $this->isiBuffer($this->kgPerPallet(), 1000.0, 'POY-RILIS-'.$user->id, 'M-'.$user->id);

            $this->actingAs($user);

            Livewire::test(Dashboard::class)
                ->assertActionVisible('rilisBuffer')
                ->callAction('rilisBuffer');
        }

        $stok = StokBarang::query()->get();

        $this->assertTrue($stok->every(fn (StokBarang $s): bool => (float) $s->stok_buffer === 0.0));
    }

    #[Test]
    public function manajer_tidak_melihat_aksi_rilis_buffer(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $this->isiBuffer($this->kgPerPallet(), 1000.0);

        $this->actingAs($this->manajer);

        Livewire::test(Dashboard::class)
            ->assertActionHidden('rilisBuffer');
    }

    #[Test]
    public function aksi_rilis_buffer_tersembunyi_di_hari_kerja(): void
    {
        $this->kunciTanggal('2026-10-19'); // Senin
        $this->isiBuffer($this->kgPerPallet(), 1000.0);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertActionHidden('rilisBuffer');
    }
}
