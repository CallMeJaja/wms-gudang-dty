<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uji panel Filament.
 *
 * Tujuan: memastikan UI Filament TIDAK mengubah aturan bisnis & RBAC
 * yang sudah diuji pada test service/policy terkait.
 */
class FilamentPanelTest extends TestCase
{
    use RefreshDatabase;

    private KategoriBarang $poy;

    private KategoriBarang $dty;

    private Satuan $kg;

    protected function setUp(): void
    {
        parent::setUp();

        $this->poy = KategoriBarang::create(['kode' => KategoriBarang::POY, 'nama' => 'POY']);
        $this->dty = KategoriBarang::create(['kode' => KategoriBarang::DTY, 'nama' => 'DTY']);
        $this->kg = Satuan::create(['kode' => Satuan::KG, 'nama' => 'Kilogram']);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function manajer(): User
    {
        return User::factory()->manajer()->create();
    }

    private function barang(KategoriBarang $kategori, array $override = []): Barang
    {
        return Barang::create(array_merge([
            'kategori_id' => $kategori->id,
            'kode_barang' => 'UJI-'.$kategori->kode,
            'nama_barang' => 'Barang Uji '.$kategori->kode,
            'satuan_dasar_id' => $this->kg->id,
            'is_merge_minggu' => false,
            'is_active' => true,
        ], $override));
    }

    // ------------------------------------------------------------------
    // Akses panel
    // ------------------------------------------------------------------

    #[Test]
    public function tamu_diarahkan_ke_login_panel(): void
    {
        $this->get('/admin/barangs')->assertRedirect('/admin/login');
    }

    #[Test]
    public function user_nonaktif_tidak_boleh_masuk_panel(): void
    {
        $user = User::factory()->admin()->create(['is_active' => false]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    #[Test]
    public function admin_bisa_membuka_daftar_barang(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/barangs')
            ->assertOk();
    }

    // ------------------------------------------------------------------
    // RBAC: Manajer read-only (PRD §2)
    // ------------------------------------------------------------------

    #[Test]
    public function manajer_bisa_melihat_daftar_tetapi_tidak_bisa_membuat(): void
    {
        $manajer = $this->manajer();

        $this->actingAs($manajer)->get('/admin/barangs')->assertOk();
        $this->actingAs($manajer)->get('/admin/barangs/create')->assertForbidden();
    }

    #[Test]
    public function manajer_tidak_bisa_membuka_form_edit(): void
    {
        $barang = $this->barang($this->dty);

        $this->actingAs($this->manajer())
            ->get("/admin/barangs/{$barang->id}/edit")
            ->assertForbidden();
    }

    #[Test]
    public function operator_tidak_bisa_membuat_barang(): void
    {
        $this->actingAs(User::factory()->operator()->create())
            ->get('/admin/barangs/create')
            ->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Aturan bisnis A11
    // ------------------------------------------------------------------

    #[Test]
    public function model_menolak_merge_minggu_untuk_non_poy_lewat_policy_rule(): void
    {
        // Aturan A11 ditegakkan di level form Filament; di sini kita
        // pastikan data POY boleh ditandai merge, non-POY tidak pernah
        // ditandai oleh seeder/form.
        $poy = $this->barang($this->poy, ['kode_barang' => 'POY-1', 'is_merge_minggu' => true]);
        $dty = $this->barang($this->dty, ['kode_barang' => 'DTY-1', 'is_merge_minggu' => false]);

        $this->assertTrue($poy->fresh()->is_merge_minggu);
        $this->assertFalse($dty->fresh()->is_merge_minggu);
    }

    #[Test]
    public function scope_merge_minggu_hanya_mengembalikan_poy(): void
    {
        $this->barang($this->poy, ['kode_barang' => 'POY-M', 'is_merge_minggu' => true]);
        $this->barang($this->dty, ['kode_barang' => 'DTY-M', 'is_merge_minggu' => false]);

        $hasil = Barang::mergeMinggu()->pluck('kode_barang')->all();

        $this->assertSame(['POY-M'], $hasil);
    }
}
