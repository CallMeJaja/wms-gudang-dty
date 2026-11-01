<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uji formal RBAC & validasi bisnis master data (F1).
 *
 * Catatan DB: phpunit.xml mengarahkan ke SQLite `:memory:` sehingga
 * database MySQL dev TIDAK tersentuh (RefreshDatabase hanya menyentuh
 * koneksi test).
 */
class MasterDataRbacTest extends TestCase
{
    use RefreshDatabase;

    private KategoriBarang $poy;

    private KategoriBarang $dty;

    private Satuan $kg;

    private Satuan $bobbin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed kategori & satuan sesuai config/wms.php (SSOT).
        $this->poy = KategoriBarang::create(['kode' => KategoriBarang::POY, 'nama' => 'POY']);
        $this->dty = KategoriBarang::create(['kode' => KategoriBarang::DTY, 'nama' => 'DTY']);

        $this->kg = Satuan::create(['kode' => Satuan::KG, 'nama' => 'Kilogram']);
        $this->bobbin = Satuan::create(['kode' => 'BOBBIN', 'nama' => 'Bobbin']);
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

    private function dataBarang(array $override = []): array
    {
        return array_merge([
            'kategori_id' => $this->dty->id,
            'kode_barang' => 'DTY-TEST-1',
            'nama_barang' => 'DTY Uji',
            'denier' => '150',
            'filament' => '48',
            'satuan_dasar_id' => $this->kg->id,
            'stok_minimum' => null,
            'is_merge_minggu' => false,
            'is_active' => true,
        ], $override);
    }

    // ------------------------------------------------------------------
    // 1. Tamu diarahkan ke login
    // ------------------------------------------------------------------

    #[Test]
    public function tamu_diarahkan_ke_login_saat_akses_master_barang(): void
    {
        $this->get('/master/barang')->assertRedirect('/login');
    }

    // ------------------------------------------------------------------
    // 2. Admin: index, create, simpan
    // ------------------------------------------------------------------

    #[Test]
    public function admin_bisa_membuka_index_create_dan_menyimpan_barang(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/master/barang')->assertOk();
        $this->actingAs($admin)->get('/master/barang/create')->assertOk();

        $response = $this->actingAs($admin)
            ->post('/master/barang', $this->dataBarang());

        $response->assertRedirect(route('master.barang.index'));
        $this->assertDatabaseHas('barang', [
            'kode_barang' => 'DTY-TEST-1',
            'nama_barang' => 'DTY Uji',
        ]);

        // Audit log tercatat.
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'CREATE',
            'modul' => 'MASTER_BARANG',
        ]);
    }

    // ------------------------------------------------------------------
    // 3. Operator: boleh index, 403 untuk operasi tulis
    // ------------------------------------------------------------------

    #[Test]
    public function operator_bisa_index_tapi_403_pada_operasi_tulis(): void
    {
        $operator = $this->operator();
        $barang = Barang::create($this->dataBarang());

        $this->actingAs($operator)->get('/master/barang')->assertOk();

        $this->actingAs($operator)->get('/master/barang/create')->assertForbidden();
        $this->actingAs($operator)->post('/master/barang', $this->dataBarang(['kode_barang' => 'DTY-OPS']))->assertForbidden();
        $this->actingAs($operator)->get("/master/barang/{$barang->id}/edit")->assertForbidden();
        $this->actingAs($operator)->put("/master/barang/{$barang->id}", $this->dataBarang())->assertForbidden();
        $this->actingAs($operator)->delete("/master/barang/{$barang->id}")->assertForbidden();
    }

    // ------------------------------------------------------------------
    // 4. Manajer: boleh index, 403 untuk create/store
    // ------------------------------------------------------------------

    #[Test]
    public function manajer_bisa_index_tapi_403_pada_create_dan_store(): void
    {
        $manajer = $this->manajer();

        $this->actingAs($manajer)->get('/master/barang')->assertOk();
        $this->actingAs($manajer)->get('/master/barang/create')->assertForbidden();
        $this->actingAs($manajer)->post('/master/barang', $this->dataBarang())->assertForbidden();
    }

    #[Test]
    public function manajer_bisa_membuka_index_semua_entitas_master(): void
    {
        $manajer = $this->manajer();

        foreach (['/master/barang', '/master/partner', '/master/grade', '/master/konversi', '/master/kategori', '/satuan'] as $url) {
            $this->actingAs($manajer)->get($url)->assertOk();
        }
    }

    // ------------------------------------------------------------------
    // 5. Validasi: is_merge_minggu=true hanya untuk POY (A11)
    // ------------------------------------------------------------------

    #[Test]
    public function merge_minggu_untuk_barang_dty_ditolak(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/master/barang', $this->dataBarang([
            'kode_barang' => 'DTY-MERGE-1',
            'is_merge_minggu' => true,
        ]));

        $response->assertSessionHasErrors('is_merge_minggu');
        $this->assertDatabaseMissing('barang', ['kode_barang' => 'DTY-MERGE-1']);
    }

    #[Test]
    public function merge_minggu_untuk_barang_poy_diterima(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/master/barang', $this->dataBarang([
            'kategori_id' => $this->poy->id,
            'kode_barang' => 'POY-MERGE-1',
            'is_merge_minggu' => true,
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('barang', [
            'kode_barang' => 'POY-MERGE-1',
            'is_merge_minggu' => 1,
        ]);
    }

    // ------------------------------------------------------------------
    // 6. Validasi: konversi untuk POY ditolak (A1b)
    // ------------------------------------------------------------------

    #[Test]
    public function konversi_satuan_untuk_barang_poy_ditolak(): void
    {
        $admin = $this->admin();
        $poy = Barang::create($this->dataBarang([
            'kategori_id' => $this->poy->id,
            'kode_barang' => 'POY-250-48',
        ]));

        $response = $this->actingAs($admin)->post('/master/konversi', [
            'barang_id' => $poy->id,
            'dari_satuan_id' => $this->bobbin->id,
            'ke_satuan_id' => $this->kg->id,
            'faktor' => 16,
        ]);

        $response->assertSessionHasErrors('barang_id');
        $this->assertDatabaseCount('konversi_satuan', 0);
    }

    // ------------------------------------------------------------------
    // 7. Grade C DTY tidak bisa dihapus
    // ------------------------------------------------------------------

    #[Test]
    public function grade_c_dty_tidak_bisa_dihapus(): void
    {
        $admin = $this->admin();
        $gradeC = Grade::create([
            'kode' => Grade::KODE_SCRAP,
            'kategori_id' => $this->dty->id,
            'keterangan' => 'DTY reject / knitting (scrap)',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/master/grade/{$gradeC->id}");

        $response->assertSessionHasErrors('grade');
        $this->assertDatabaseHas('grade', [
            'id' => $gradeC->id,
            'kode' => 'C',
            'kategori_id' => $this->dty->id,
        ]);
    }

    #[Test]
    public function grade_c_dty_tidak_bisa_dinonaktifkan(): void
    {
        $admin = $this->admin();
        $gradeC = Grade::create([
            'kode' => Grade::KODE_SCRAP,
            'kategori_id' => $this->dty->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put("/master/grade/{$gradeC->id}", [
            'kode' => 'C',
            'kategori_id' => $this->dty->id,
            'keterangan' => null,
            'is_active' => false,
        ]);

        $response->assertSessionHasErrors('is_active');
        $this->assertDatabaseHas('grade', ['id' => $gradeC->id, 'is_active' => 1]);
    }

    // ------------------------------------------------------------------
    // Tambahan: data referensi kritis tidak bisa dihapus bila dipakai
    // ------------------------------------------------------------------

    #[Test]
    public function kategori_yang_sudah_dipakai_tidak_bisa_dihapus(): void
    {
        $admin = $this->admin();
        Barang::create($this->dataBarang());

        $response = $this->actingAs($admin)->delete("/master/kategori/{$this->dty->id}");

        $response->assertSessionHasErrors('kategori');
        $this->assertDatabaseHas('kategori_barang', ['id' => $this->dty->id]);
    }

    #[Test]
    public function satuan_yang_sudah_dipakai_tidak_bisa_dihapus(): void
    {
        $admin = $this->admin();
        Barang::create($this->dataBarang());

        $response = $this->actingAs($admin)->delete("/satuan/{$this->kg->id}");

        $response->assertSessionHasErrors('satuan');
        $this->assertDatabaseHas('satuan', ['id' => $this->kg->id]);
    }

    #[Test]
    public function admin_bisa_menghapus_barang_secara_soft_delete(): void
    {
        $admin = $this->admin();
        $barang = Barang::create($this->dataBarang());

        $this->actingAs($admin)->delete("/master/barang/{$barang->id}")
            ->assertRedirect(route('master.barang.index'));

        $this->assertSoftDeleted('barang', ['id' => $barang->id]);
        $this->assertDatabaseHas('activity_logs', ['event' => 'DELETE', 'modul' => 'MASTER_BARANG']);
    }
}
