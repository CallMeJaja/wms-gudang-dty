<?php

namespace Tests\Feature;

use App\Filament\Resources\StokBarangs\Pages\ListStokBarangs;
use App\Models\StokBarang;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji resource Stok Barang (F4) — baca-saja untuk semua role aktif.
 */
class FilamentStokBarangTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function stok(): StokBarang
    {
        $barang = $this->barangPoy(true, 'POY-STOK-1');

        return StokBarang::create([
            'barang_id' => $barang->id,
            'merge' => '4P1044',
            'grade_id' => $this->gradePoyAl->id,
            'stok_aktif' => 1000,
            'stok_buffer' => 576,
        ]);
    }

    #[Test]
    public function semua_role_bisa_membuka_daftar_stok_barang(): void
    {
        $this->stok();

        foreach ([
            User::factory()->admin()->create(),
            User::factory()->operator()->create(),
            User::factory()->manajer()->create(),
        ] as $user) {
            $this->actingAs($user)
                ->get('/admin/stok-barangs')
                ->assertOk();
        }
    }

    #[Test]
    public function tabel_menampilkan_total_dan_pallet(): void
    {
        $stok = $this->stok();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ListStokBarangs::class)
            ->assertCanSeeTableRecords([$stok])
            ->assertSee('1.576,00')
            ->assertSee('2,74');
    }

    #[Test]
    public function halaman_create_dan_edit_tidak_tersedia(): void
    {
        $stok = $this->stok();

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/stok-barangs/create')->assertNotFound();
        $this->get("/admin/stok-barangs/{$stok->id}/edit")->assertNotFound();
    }
}
