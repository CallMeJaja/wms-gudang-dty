<?php

namespace Tests\Feature;

use App\Enums\JenisMutasi;
use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use App\Filament\Resources\WasteLogs\Pages\CreateWasteLog;
use App\Filament\Resources\WasteLogs\Pages\ListWasteLogs;
use App\Filament\Resources\WasteLogs\WasteLogResource;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\User;
use App\Models\WasteLog;
use App\Services\StokService;
use Database\Seeders\WasteLogSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

class FilamentWasteLogTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    private User $nonaktif;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();
        $this->nonaktif = User::factory()->operator()->create(['is_active' => false]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function admin_dan_operator_bisa_mencatat_waste_log(): void
    {
        foreach ([$this->admin, $this->operator] as $user) {
            $this->actingAs($user);

            Livewire::test(CreateWasteLog::class)
                ->fillForm([
                    'tanggal' => '2026-11-05',
                    'jenis' => WasteLog::JENIS_WASTE,
                    'tujuan' => 'RCL',
                    'berat_kg' => 125.5,
                    'keterangan' => 'Waste benang '.$user->role->value,
                ])
                ->call('create')
                ->assertHasNoFormErrors();
        }

        $this->assertSame(2, WasteLog::query()->count());
        $this->assertDatabaseHas('waste_logs', [
            'jenis' => WasteLog::JENIS_WASTE,
            'tujuan' => 'RCL',
            'berat_kg' => 125.50,
            'created_by' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('waste_logs', [
            'created_by' => $this->operator->id,
        ]);
    }

    #[Test]
    public function manajer_read_only_dan_pengguna_nonaktif_ditolak(): void
    {
        $log = WasteLog::create([
            'tanggal' => '2026-11-05',
            'jenis' => WasteLog::JENIS_REJECT,
            'tujuan' => 'EYD',
            'berat_kg' => 10,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->manajer);
        $this->get(WasteLogResource::getUrl())->assertOk();
        $this->get(WasteLogResource::getUrl('create'))->assertForbidden();
        $this->get(WasteLogResource::getUrl('edit', ['record' => $log]))->assertForbidden();
        $this->assertTrue(Gate::forUser($this->manajer)->denies('update', $log));
        $this->assertTrue(Gate::forUser($this->manajer)->denies('delete', $log));

        $this->actingAs($this->nonaktif);
        $this->get(WasteLogResource::getUrl())->assertForbidden();
        $this->assertTrue(Gate::forUser($this->nonaktif)->denies('create', WasteLog::class));
    }

    #[Test]
    public function waste_log_tidak_mengubah_stok_maupun_mutasi(): void
    {
        $barang = $this->barangDty();
        $stok = app(StokService::class);
        $stok->tambahStok($barang, '4D9160', $this->gradeDtyA->id, 100);

        $stokSebelum = (float) StokBarang::query()->sole()->stok_aktif;
        $mutasiSebelum = MutasiStok::query()->count();

        $this->actingAs($this->admin);

        Livewire::test(CreateWasteLog::class)
            ->fillForm([
                'tanggal' => '2026-11-05',
                'jenis' => WasteLog::JENIS_REJECT,
                'tujuan' => 'EYD',
                'berat_kg' => 25.75,
                'keterangan' => 'Reject tanpa pengurangan stok.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($stokSebelum, (float) StokBarang::query()->sole()->stok_aktif);
        $this->assertSame($mutasiSebelum, MutasiStok::query()->count());
        $this->assertSame(0, MutasiStok::query()->where('jenis_transaksi', JenisMutasi::Keluar->value)->count());
    }

    #[Test]
    public function tabel_menampilkan_field_waste_tanpa_grade_atau_merge(): void
    {
        $log = WasteLog::create([
            'tanggal' => '2026-11-05',
            'jenis' => WasteLog::JENIS_SCRAP_PACKAGING,
            'tujuan' => 'Pengepul',
            'berat_kg' => 42.5,
            'keterangan' => 'Scrap kemasan.',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(ListWasteLogs::class)
            ->assertCanSeeTableRecords([$log])
            ->assertSee('Waste / Scrap')
            ->assertSee('Scrap Packaging')
            ->assertSee('Pengepul')
            ->assertSee('42,50')
            ->assertDontSee('Grade')
            ->assertDontSee('Merge');
    }

    #[Test]
    public function waste_log_seeder_idempoten(): void
    {
        $this->seed(WasteLogSeeder::class);
        $jumlah = WasteLog::query()->count();

        $this->seed(WasteLogSeeder::class);

        $this->assertGreaterThan(0, $jumlah);
        $this->assertSame($jumlah, WasteLog::query()->count());
    }

    #[Test]
    public function resource_scrap_lama_tidak_tampil_di_navigasi(): void
    {
        $this->assertFalse(PengeluaranScrapResource::shouldRegisterNavigation());

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Scrap / Reject');
    }
}
