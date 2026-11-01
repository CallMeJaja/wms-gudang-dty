<?php

namespace Tests\Feature;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Models\ActivityLog;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\User;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji dashboard & rilis buffer Minggu (F4, PRD §5.2.3–§5.2.4).
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class DashboardTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private StokService $stok;

    private User $admin;

    private User $operator;

    private User $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->stok = $this->app->make(StokService::class);
        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();
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

    private function isiBuffer(float $bufferKg, float $aktifKg = 0.0): void
    {
        $barang = $this->barangPoy(true);

        if ($aktifKg > 0) {
            $this->stok->tambahStok($barang, 'M-1', $this->gradePoyAl->id, $aktifKg, PosStok::Aktif, JenisMutasi::Masuk, 'UJI-AKTIF');
        }

        if ($bufferKg > 0) {
            $this->stok->tambahStok($barang, 'M-1', $this->gradePoyAl->id, $bufferKg, PosStok::Buffer, JenisMutasi::Masuk, 'UJI-BUFFER');
        }
    }

    #[Test]
    public function tamu_dialihkan_ke_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    #[Test]
    public function ketiga_role_bisa_buka_dashboard(): void
    {
        $this->kunciHariKerja();

        foreach ([$this->admin, $this->operator, $this->manajer] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertOk();
        }
    }

    #[Test]
    public function widget_menampilkan_angka_buffer_sesuai_data(): void
    {
        $this->kunciHariKerja();
        $this->isiBuffer($this->kgPerPallet());

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1,00 / 6 pallet', false)
            ->assertSee('Buffer Minggu', false);
    }

    #[Test]
    public function status_siap_saat_mencapai_target(): void
    {
        $this->kunciHariKerja();
        $this->isiBuffer($this->targetBufferKg());

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Siap', false);
    }

    #[Test]
    public function status_belum_siap_saat_kurang(): void
    {
        $this->kunciHariKerja(); // Selasa: sisa 5 hari kerja
        $this->isiBuffer($this->kgPerPallet());

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum Siap', false)
            ->assertSee('Sisa 5 hari kerja', false);
    }

    #[Test]
    public function rilis_minggu_memindahkan_buffer_ke_aktif(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $buffer = $this->kgPerPallet();
        $aktif = 1000.0;
        $this->isiBuffer($buffer, $aktif);

        $this->actingAs($this->admin)->post(route('dashboard.rilis-buffer'))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $stok = StokBarang::query()->sole();
        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame($aktif + $buffer, (float) $stok->stok_aktif);
        $this->assertSame(2, MutasiStok::query()->where('jenis_transaksi', JenisMutasi::RilisBuffer)->count());
        $this->assertSame(1, ActivityLog::query()->where('event', 'RELEASE_BUFFER')->count());
    }

    #[Test]
    public function rilis_idempoten_kedua_kali_nol_tanpa_error(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $this->isiBuffer($this->kgPerPallet(), 500.0);

        $this->actingAs($this->admin)->post(route('dashboard.rilis-buffer'))->assertRedirect();

        $sesudahPertama = StokBarang::query()->sole()->replicate();

        $this->actingAs($this->admin)->post(route('dashboard.rilis-buffer'))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $stok = StokBarang::query()->sole();
        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame((float) $sesudahPertama->stok_aktif, (float) $stok->stok_aktif);
    }

    #[Test]
    public function rilis_hari_kerja_ditolak(): void
    {
        $this->kunciTanggal('2026-10-19'); // Senin
        $this->isiBuffer($this->kgPerPallet(), 500.0);

        $this->actingAs($this->admin)->post(route('dashboard.rilis-buffer'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $stok = StokBarang::query()->sole();
        $this->assertSame($this->kgPerPallet(), (float) $stok->stok_buffer);
        $this->assertSame(0, MutasiStok::query()->where('jenis_transaksi', JenisMutasi::RilisBuffer)->count());
    }

    #[Test]
    public function operator_boleh_rilis(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $this->isiBuffer($this->kgPerPallet());

        $this->actingAs($this->operator)->post(route('dashboard.rilis-buffer'))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertSame(0.0, (float) StokBarang::query()->sole()->stok_buffer);
    }

    #[Test]
    public function manajer_ditolak_403(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $this->isiBuffer($this->kgPerPallet());

        $this->actingAs($this->manajer)->post(route('dashboard.rilis-buffer'))->assertForbidden();
    }

    #[Test]
    public function command_minggu_merilis_buffer(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $this->isiBuffer($this->kgPerPallet());

        $this->artisan('wms:rilis-buffer-minggu')
            ->expectsOutputToContain('dirilis')
            ->assertExitCode(0);

        $this->assertSame(0.0, (float) StokBarang::query()->sole()->stok_buffer);
    }

    #[Test]
    public function command_hari_kerja_dilewati(): void
    {
        $this->kunciTanggal('2026-10-19'); // Senin
        $this->isiBuffer($this->kgPerPallet());

        $this->artisan('wms:rilis-buffer-minggu')
            ->expectsOutputToContain('dilewati')
            ->assertExitCode(0);

        $this->assertSame($this->kgPerPallet(), (float) StokBarang::query()->sole()->stok_buffer);
    }
}
