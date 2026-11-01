<?php

namespace Tests\Feature;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Models\SupplierCustomer;
use App\Models\TransaksiMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uji resource Penerimaan Filament (PoC repeater + action posting/void).
 *
 * Fokus: tabel render, RBAC halaman, dan integrasi action
 * ke PenerimaanService / VoidTransaksiService yang sudah ada.
 */
class FilamentTransaksiMasukTest extends TestCase
{
    use RefreshDatabase;

    private KategoriBarang $poy;

    private Satuan $kg;

    private Grade $grade;

    private Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->poy = KategoriBarang::create(['kode' => KategoriBarang::POY, 'nama' => 'POY']);
        $this->kg = Satuan::create(['kode' => Satuan::KG, 'nama' => 'Kilogram']);

        $this->grade = Grade::create([
            'kode' => 'A',
            'kategori_id' => $this->poy->id,
            'is_active' => true,
        ]);

        $this->barang = Barang::create([
            'kategori_id' => $this->poy->id,
            'kode_barang' => 'POY-150-48',
            'nama_barang' => 'POY 150/48',
            'denier' => '150',
            'filament' => '48',
            'satuan_dasar_id' => $this->kg->id,
            'is_merge_minggu' => true,
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function operator(): User
    {
        return User::factory()->operator()->create();
    }

    private function manajer(): User
    {
        return User::factory()->manajer()->create();
    }

    private ?SupplierCustomer $ipci = null;

    private static int $nomorUrut = 0;

    private function draf(array $override = []): TransaksiMasuk
    {
        $this->ipci ??= SupplierCustomer::create([
            'kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'SUPPLIER',
        ]);

        self::$nomorUrut++;

        return TransaksiMasuk::create(array_merge([
            'nomor_transaksi' => 'FIL-'.str_pad((string) self::$nomorUrut, 4, '0', STR_PAD_LEFT),
            'jenis_masuk' => JenisMasuk::PoyIpci,
            'tanggal' => now()->toDateString(),
            'partner_id' => $this->ipci->id,
            'status' => StatusDokumen::Draft,
            'created_by' => $this->admin()->id,
        ], $override));
    }

    // ------------------------------------------------------------------
    // Halaman
    // ------------------------------------------------------------------

    #[Test]
    public function admin_bisa_membuka_daftar_dan_form_penerimaan(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/transaksi-masuks')->assertOk();
        $this->get('/admin/transaksi-masuks/create')->assertOk();
    }

    #[Test]
    public function operator_bisa_membuat_penerimaan_tetapi_manajer_tidak(): void
    {
        $this->actingAs($this->operator())
            ->get('/admin/transaksi-masuks/create')
            ->assertOk();

        $this->actingAs($this->manajer())
            ->get('/admin/transaksi-masuks')
            ->assertOk();

        $this->actingAs($this->manajer())
            ->get('/admin/transaksi-masuks/create')
            ->assertForbidden();
    }

    #[Test]
    public function manajer_tidak_bisa_membuka_edit_penerimaan(): void
    {
        $trx = $this->draf();

        $this->actingAs($this->manajer())
            ->get("/admin/transaksi-masuks/{$trx->id}/edit")
            ->assertForbidden();
    }

    #[Test]
    public function edit_hanya_untuk_draf(): void
    {
        $draf = $this->draf(['nomor_transaksi' => 'D-1']);
        $posted = $this->draf([
            'nomor_transaksi' => 'P-1',
            'status' => StatusDokumen::Posted,
        ]);

        $this->actingAs($this->admin());

        $this->get("/admin/transaksi-masuks/{$draf->id}/edit")->assertOk();
        $this->get("/admin/transaksi-masuks/{$posted->id}/edit")->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Policy (pemakaian ulang, tanpa perubahan)
    // ------------------------------------------------------------------

    #[Test]
    public function policy_penerimaan_tetap_berlaku_di_resource(): void
    {
        $draf = $this->draf();

        $admin = $this->admin();
        $operator = $this->operator();
        $manajer = $this->manajer();

        $this->assertTrue($admin->can('create', TransaksiMasuk::class));
        $this->assertTrue($admin->can('update', $draf));
        $this->assertTrue($admin->can('void', $draf) || $draf->status === StatusDokumen::Draft);

        $this->assertTrue($operator->can('create', TransaksiMasuk::class));
        $this->assertTrue($operator->can('update', $draf));
        $this->assertFalse($operator->can('void', $draf));

        $this->assertFalse($manajer->can('create', TransaksiMasuk::class));
        $this->assertFalse($manajer->can('update', $draf));
        $this->assertFalse($manajer->can('void', $draf));
    }

    #[Test]
    public function void_hanya_admin_dan_hanya_posted(): void
    {
        $posted = $this->draf([
            'nomor_transaksi' => 'P-VOID',
            'status' => StatusDokumen::Posted,
        ]);

        $this->assertTrue($this->admin()->can('void', $posted));
        $this->assertFalse($this->operator()->can('void', $posted));
        $this->assertFalse($this->manajer()->can('void', $posted));
    }
}
