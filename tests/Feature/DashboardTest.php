<?php

namespace Tests\Feature;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Models\ActivityLog;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\User;
use App\Services\AlokasiBufferService;
use App\Services\RilisBufferService;
use App\Services\StatusBufferMinggu;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji service buffer Minggu: status buffer, rilis buffer, dan perintah terjadwal.
 *
 * UI dashboard diuji oleh FilamentDashboardWidgetsTest; file ini
 * mempertahankan cakupan service (status buffer, rilis service, perintah
 * terjadwal).
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class DashboardTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private StokService $stok;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->stok = $this->app->make(StokService::class);
        $this->admin = User::factory()->admin()->create();
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
    public function status_buffer_siap_saat_mencapai_target(): void
    {
        $this->kunciTanggal('2026-10-13');
        $this->isiBuffer($this->targetBufferKg());

        $status = $this->app->make(AlokasiBufferService::class)->statusBuffer();

        $this->assertSame(StatusBufferMinggu::SIAP, $status->level);
        $this->assertSame('Siap', $status->label);
        $this->assertSame($this->targetBufferKg(), $status->bufferKg);
    }

    #[Test]
    public function status_buffer_belum_siap_saat_kurang(): void
    {
        $this->kunciTanggal('2026-10-13'); // Selasa: sisa 5 hari kerja
        $this->isiBuffer($this->kgPerPallet() / 2);

        $status = $this->app->make(AlokasiBufferService::class)->statusBuffer();

        $this->assertSame(StatusBufferMinggu::BELUM_SIAP, $status->level);
        $this->assertSame('Belum Siap', $status->label);
        $this->assertSame(5, $status->sisaHariKerja);
        $this->assertSame(0.5, $status->bufferPallet);
    }

    #[Test]
    public function rilis_minggu_memindahkan_buffer_ke_aktif(): void
    {
        $this->kunciTanggal('2026-10-18'); // Minggu
        $buffer = $this->kgPerPallet();
        $aktif = 1000.0;
        $this->isiBuffer($buffer, $aktif);

        $hasil = $this->app->make(RilisBufferService::class)->rilis(now(), $this->admin->id);

        $this->assertSame($buffer, $hasil['dirilis_kg']);
        $this->assertSame(1, $hasil['baris']);

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

        $service = $this->app->make(RilisBufferService::class);
        $service->rilis(now(), $this->admin->id);

        $sesudahPertama = StokBarang::query()->sole()->replicate();

        $kedua = $service->rilis(now(), $this->admin->id);

        $this->assertSame(0.0, $kedua['dirilis_kg']);
        $this->assertSame(0, $kedua['baris']);

        $stok = StokBarang::query()->sole();
        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame((float) $sesudahPertama->stok_aktif, (float) $stok->stok_aktif);
    }

    #[Test]
    public function rilis_otomatis_hanya_boleh_hari_minggu(): void
    {
        $service = $this->app->make(RilisBufferService::class);

        $this->assertTrue($service->bolehRilisOtomatis($this->kunciTanggal('2026-10-18'))); // Minggu
        $this->assertFalse($service->bolehRilisOtomatis($this->kunciTanggal('2026-10-19'))); // Senin
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
