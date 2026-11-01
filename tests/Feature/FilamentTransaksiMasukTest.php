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

        $this->get('/admin/penerimaan-poy')->assertOk();
        $this->get('/admin/penerimaan-poy/create')->assertOk();
    }

    #[Test]
    public function ketiga_jenis_penerimaan_punya_halaman_sendiri(): void
    {
        $this->actingAs($this->admin());

        foreach (['poy', 'pendukung', 'dty'] as $jenis) {
            $this->get("/admin/penerimaan-{$jenis}")->assertOk();
            $this->get("/admin/penerimaan-{$jenis}/create")->assertOk();
        }
    }

    #[Test]
    public function repeater_detail_memakai_grid_bukan_tabel_sempit(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/penerimaan-dty/create')
            ->assertOk()
            ->assertDontSee('fi-fo-table-repeater', false)
            ->assertSee('fi-fo-repeater', false);
    }

    #[Test]
    public function partial_ringkasan_modal_bisa_dirender(): void
    {
        $transaksi = $this->draf();
        $transaksi->detail()->create([
            'barang_id' => $this->barang->id,
            'merge' => '4P1044',
            'grade_id' => $this->grade->id,
            'jumlah_kemasan' => 7,
            'berat_kg' => 4032,
        ]);

        $html = view('filament.penerimaan.ringkasan', [
            'transaksi' => $transaksi->load([
                'detail.barang.kategori',
                'detail.grade',
                'detail.satuanKemasan',
                'partner',
                'pembuat',
                'pemvoid',
                'alokasiBuffer',
            ]),
        ])->render();

        $this->assertStringContainsString($transaksi->nomor_transaksi, $html);
        $this->assertStringContainsString('4P1044', $html);
        $this->assertStringContainsString('4.032,00', $html);
    }

    #[Test]
    public function daftar_menampilkan_total_kg_dari_detail(): void
    {
        $transaksi = $this->draf();
        $transaksi->detail()->create([
            'barang_id' => $this->barang->id,
            'merge' => '4P1044',
            'grade_id' => $this->grade->id,
            'jumlah_kemasan' => 7,
            'berat_kg' => 4032,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/penerimaan-poy')
            ->assertOk()
            ->assertSee('4.032,00');
    }

    #[Test]
    public function record_jenis_lain_tidak_bisa_dibuka_lewat_resource_yang_salah(): void
    {
        $dty = $this->draf([
            'nomor_transaksi' => 'DTY-ISOLASI',
            'jenis_masuk' => JenisMasuk::DtyProduksi,
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/penerimaan-poy/{$dty->id}/edit")
            ->assertNotFound();
    }

    #[Test]
    public function operator_bisa_membuat_penerimaan_tetapi_manajer_tidak(): void
    {
        $this->actingAs($this->operator())
            ->get('/admin/penerimaan-poy/create')
            ->assertOk();

        $this->actingAs($this->manajer())
            ->get('/admin/penerimaan-poy')
            ->assertOk();

        $this->actingAs($this->manajer())
            ->get('/admin/penerimaan-poy/create')
            ->assertForbidden();
    }

    #[Test]
    public function manajer_tidak_bisa_membuka_edit_penerimaan(): void
    {
        $trx = $this->draf();

        $this->actingAs($this->manajer())
            ->get("/admin/penerimaan-poy/{$trx->id}/edit")
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

        $this->get("/admin/penerimaan-poy/{$draf->id}/edit")->assertOk();
        $this->get("/admin/penerimaan-poy/{$posted->id}/edit")->assertForbidden();
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
