<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Filament\Resources\StokOpnames\Pages\CreateStokOpname;
use App\Models\StokOpname;
use App\Models\User;
use App\Services\StokOpnameService;
use App\Services\StokService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji resource Stok Opname di panel Filament.
 *
 * Mutasi stok tetap sepenuhnya lewat StokOpnameService — test memastikan
 * form hanya menjadi pintu input dan RBAC Admin tetap berlaku.
 */
class FilamentStokOpnameTest extends TestCase
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

    private function payload(array $override = []): array
    {
        return array_merge([
            'tanggal' => '2026-11-01',
            'merge' => 'M1',
            'stok_fisik_qty' => 100,
        ], $override);
    }

    #[Test]
    public function admin_bisa_membuka_index_dan_create(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/stok-opnames')->assertOk();
        $this->get('/admin/stok-opnames/create')->assertOk();
    }

    #[Test]
    public function operator_dan_manajer_ditolak_403(): void
    {
        foreach ([$this->operator, $this->manajer] as $user) {
            $this->actingAs($user);

            $this->get('/admin/stok-opnames')->assertForbidden();
            $this->get('/admin/stok-opnames/create')->assertForbidden();
        }
    }

    #[Test]
    public function selisih_positif_membuat_adjustment_in_dan_menaikkan_stok(): void
    {
        $barang = $this->barangDty('DTY-OPN-1');
        app(StokService::class)->tambahStok($barang, 'M1', $this->gradeDtyA->id, 100);

        $this->actingAs($this->admin);

        Livewire::test(CreateStokOpname::class)
            ->fillForm($this->payload([
                'barang_id' => $barang->id,
                'grade_id' => $this->gradeDtyA->id,
                'stok_fisik_qty' => 130,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $opname = StokOpname::query()->latest('id')->firstOrFail();

        $this->assertNotNull($opname->transaksi_masuk_id);
        $this->assertNull($opname->transaksi_keluar_id);
        $this->assertSame(JenisMasuk::AdjustmentIn, $opname->transaksiMasuk->jenis_masuk);
        $this->assertSame(30.0, (float) $opname->transaksiMasuk->detail()->sole()->berat_kg);

        $saldo = app(StokService::class)->saldo($barang, 'M1', $this->gradeDtyA->id);
        $this->assertSame(130.0, (float) $saldo->stok_aktif);
    }

    #[Test]
    public function selisih_negatif_membuat_adjustment_out_dan_memotong_aktif_lalu_buffer(): void
    {
        $barang = $this->barangPoy(true, 'POY-OPN-1');
        $stok = app(StokService::class);

        $stok->tambahStok($barang, 'M1', $this->gradePoyAl->id, 500, PosStok::Aktif, JenisMutasi::Masuk, 'UJI-AKTIF');
        $stok->tambahStok($barang, 'M1', $this->gradePoyAl->id, 400, PosStok::Buffer, JenisMutasi::Masuk, 'UJI-BUFFER');

        $this->actingAs($this->admin);

        Livewire::test(CreateStokOpname::class)
            ->fillForm($this->payload([
                'barang_id' => $barang->id,
                'grade_id' => $this->gradePoyAl->id,
                'stok_fisik_qty' => 200,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $opname = StokOpname::query()->latest('id')->firstOrFail();

        $this->assertNotNull($opname->transaksi_keluar_id);
        $this->assertNull($opname->transaksi_masuk_id);
        $this->assertSame(JenisKeluar::AdjustmentOut, $opname->transaksiKeluar->jenis_keluar);

        $saldo = $stok->saldo($barang, 'M1', $this->gradePoyAl->id);

        $this->assertSame(0.0, (float) $saldo->stok_aktif);
        $this->assertSame(200.0, (float) $saldo->stok_buffer);
    }

    #[Test]
    public function selisih_nol_tidak_membuat_transaksi(): void
    {
        $barang = $this->barangDty('DTY-OPN-2');
        app(StokService::class)->tambahStok($barang, 'M1', $this->gradeDtyA->id, 100);

        $this->actingAs($this->admin);

        Livewire::test(CreateStokOpname::class)
            ->fillForm($this->payload([
                'barang_id' => $barang->id,
                'grade_id' => $this->gradeDtyA->id,
                'stok_fisik_qty' => 100,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $opname = StokOpname::query()->latest('id')->firstOrFail();

        $this->assertNull($opname->transaksi_masuk_id);
        $this->assertNull($opname->transaksi_keluar_id);
    }

    #[Test]
    public function opname_duplikat_tanggal_sama_ditolak_di_form(): void
    {
        $barang = $this->barangDty('DTY-OPN-3');
        app(StokService::class)->tambahStok($barang, 'M1', $this->gradeDtyA->id, 100);

        $this->actingAs($this->admin);

        Livewire::test(CreateStokOpname::class)
            ->fillForm($this->payload([
                'barang_id' => $barang->id,
                'grade_id' => $this->gradeDtyA->id,
                'stok_fisik_qty' => 100,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateStokOpname::class)
            ->fillForm($this->payload([
                'barang_id' => $barang->id,
                'grade_id' => $this->gradeDtyA->id,
                'stok_fisik_qty' => 100,
            ]))
            ->call('create')
            ->assertHasFormErrors(['stok_fisik_qty']);

        $this->assertSame(1, StokOpname::query()->count());
    }

    #[Test]
    public function grade_beda_kategori_ditolak_di_form(): void
    {
        $barang = $this->barangPoy(true, 'POY-OPN-2');

        $this->actingAs($this->admin);

        Livewire::test(CreateStokOpname::class)
            ->fillForm($this->payload([
                'barang_id' => $barang->id,
                'grade_id' => $this->gradeDtyA->id,
                'stok_fisik_qty' => 100,
            ]))
            ->call('create')
            ->assertHasFormErrors(['grade_id']);

        $this->assertSame(0, StokOpname::query()->count());
    }

    #[Test]
    public function partial_ringkasan_merender_nomor_dan_selisih(): void
    {
        $barang = $this->barangDty('DTY-OPN-4');
        app(StokService::class)->tambahStok($barang, 'M1', $this->gradeDtyA->id, 100);

        $opname = app(StokOpnameService::class)->proses(
            $barang,
            'M1',
            $this->gradeDtyA->id,
            125,
            '2026-11-01',
            $this->admin->id,
            'Uji ringkasan',
        );

        $this->view('filament.stok-opname.ringkasan', [
            'opname' => $opname->load(['barang.kategori', 'grade', 'pembuat', 'transaksiMasuk', 'transaksiKeluar']),
        ])
            ->assertSee($opname->nomor_opname)
            ->assertSee('25,00')
            ->assertSee('Stok sistem');
    }
}
