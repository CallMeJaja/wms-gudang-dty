<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Filament\Pages\Dashboard;
use App\Filament\Support\DashboardData;
use App\Models\StokBarang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
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
 * Uji dashboard Filament: widget, subheading, dan aksi rilis buffer.
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
                ->assertSee('Draf Menunggu Posting')
                ->assertSee('Stok Harian 30 Hari')
                ->assertSee('Perlu Perhatian');
        }
    }

    #[Test]
    public function draf_menunggu_posting_menghitung_draf_penerimaan_dan_pengeluaran(): void
    {
        $this->kunciHariKerja();

        TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-DRAF-1',
            'jenis_masuk' => JenisMasuk::PoyIpci,
            'tanggal' => '2026-10-13',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        TransaksiKeluar::create([
            'nomor_transaksi' => 'OUT-DRAF-1',
            'jenis_keluar' => JenisKeluar::PoyProduksi,
            'tanggal' => '2026-10-13',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        $draf = DashboardData::drafMenungguPosting();

        $this->assertSame(1, $draf['penerimaan']);
        $this->assertSame(1, $draf['pengeluaran']);
        $this->assertSame(2, $draf['total']);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Penerimaan 1 · Pengeluaran 1');
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
