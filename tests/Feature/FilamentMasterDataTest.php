<?php

namespace Tests\Feature;

use App\Filament\Resources\Grades\GradeResource;
use App\Filament\Resources\Grades\Pages\CreateGrade;
use App\Filament\Resources\Grades\Pages\EditGrade;
use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Filament\Resources\Kategoris\KategoriResource;
use App\Filament\Resources\Kategoris\Pages\CreateKategori;
use App\Filament\Resources\Kategoris\Pages\EditKategori;
use App\Filament\Resources\Kategoris\Pages\ListKategoris;
use App\Filament\Resources\KonversiSatuans\KonversiSatuanResource;
use App\Filament\Resources\KonversiSatuans\Pages\CreateKonversiSatuan;
use App\Filament\Resources\KonversiSatuans\Pages\EditKonversiSatuan;
use App\Filament\Resources\KonversiSatuans\Pages\ListKonversiSatuans;
use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\Pages\ListPartners;
use App\Filament\Resources\Partners\PartnerResource;
use App\Filament\Resources\Satuans\Pages\CreateSatuan;
use App\Filament\Resources\Satuans\Pages\EditSatuan;
use App\Filament\Resources\Satuans\Pages\ListSatuans;
use App\Filament\Resources\Satuans\SatuanResource;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\SupplierCustomer;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji Fase 1 (Master Data) di panel Filament.
 *
 * DB memakai SQLite :memory: dari phpunit.xml sehingga database dev
 * tidak tersentuh.
 */
class FilamentMasterDataTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    private SupplierCustomer $partner;

    private Grade $gradeCDty;

    private KonversiSatuan $konversiGlobal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();

        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();

        $this->partner = SupplierCustomer::create([
            'kode' => 'SUP-1',
            'nama' => 'Supplier Uji',
            'jenis' => 'SUPPLIER',
        ]);

        $this->gradeCDty = Grade::create([
            'kode' => Grade::KODE_SCRAP,
            'kategori_id' => $this->kategoriDty->id,
            'keterangan' => 'DTY reject / knitting (scrap)',
            'is_active' => true,
        ]);

        $this->konversiGlobal = KonversiSatuan::create([
            'barang_id' => null,
            'dari_satuan_id' => $this->satuanBobbin->id,
            'ke_satuan_id' => $this->satuanBox->id,
            'faktor' => 16,
            'is_default' => false,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    private function entitas(): array
    {
        return [
            'partner' => [PartnerResource::class, 'partners'],
            'grade' => [GradeResource::class, 'grades'],
            'kategori' => [KategoriResource::class, 'kategori-barangs'],
            'satuan' => [SatuanResource::class, 'satuans'],
            'konversi' => [KonversiSatuanResource::class, 'konversi-satuans'],
        ];
    }

    private function recordUntuk(string $kunci): Model
    {
        return match ($kunci) {
            'partner' => $this->partner,
            'grade' => $this->gradeDtyA,
            'kategori' => $this->kategoriDty,
            'satuan' => $this->satuanKg,
            'konversi' => $this->konversiGlobal,
        };
    }

    // ------------------------------------------------------------------
    // Akses halaman
    // ------------------------------------------------------------------

    #[Test]
    public function admin_bisa_membuka_semua_index_dan_form_create_master_data(): void
    {
        $this->actingAs($this->admin);

        foreach ($this->entitas() as [$resource, $slug]) {
            $this->get("/admin/{$slug}")->assertOk();
            $this->get("/admin/{$slug}/create")->assertOk();
        }
    }

    #[Test]
    public function operator_dan_manajer_bisa_index_tetapi_403_pada_create_dan_edit(): void
    {
        foreach ([$this->operator, $this->manajer] as $user) {
            $this->actingAs($user);

            foreach ($this->entitas() as $kunci => [$resource, $slug]) {
                $record = $this->recordUntuk($kunci);

                $this->get("/admin/{$slug}")->assertOk();
                $this->get("/admin/{$slug}/create")->assertForbidden();
                $this->get("/admin/{$slug}/{$record->getKey()}/edit")->assertForbidden();

                $this->assertFalse($resource::canDelete($record));
            }
        }
    }

    #[Test]
    public function operator_dan_manajer_tidak_melihat_aksi_hapus_di_tabel(): void
    {
        foreach ([$this->operator, $this->manajer] as $user) {
            $this->actingAs($user);

            Livewire::test(ListPartners::class)
                ->assertTableActionHidden('delete', $this->partner);

            Livewire::test(ListSatuans::class)
                ->assertTableActionHidden('delete', $this->satuanKg);
        }
    }

    // ------------------------------------------------------------------
    // Simpan & ubah
    // ------------------------------------------------------------------

    #[Test]
    public function admin_bisa_membuat_dan_mengubah_partner(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'kode' => 'CUST-9',
                'nama' => 'Customer Baru',
                'jenis' => 'CUSTOMER',
                'kontak' => '0812',
                'alamat' => 'Jl. Uji',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $partner = SupplierCustomer::query()->where('kode', 'CUST-9')->firstOrFail();

        Livewire::test(EditPartner::class, ['record' => $partner->getKey()])
            ->fillForm(['nama' => 'Customer Diubah'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Customer Diubah', $partner->fresh()->nama);
        $this->assertDatabaseHas('activity_logs', ['event' => 'CREATE', 'modul' => 'MASTER_PARTNER']);
        $this->assertDatabaseHas('activity_logs', ['event' => 'UPDATE', 'modul' => 'MASTER_PARTNER']);
    }

    #[Test]
    public function admin_bisa_membuat_dan_mengubah_kategori(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateKategori::class)
            ->fillForm([
                'kode' => 'LAIN',
                'nama' => 'Kategori Lain',
                'keterangan' => 'Uji',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $kategori = KategoriBarang::query()->where('kode', 'LAIN')->firstOrFail();

        Livewire::test(EditKategori::class, ['record' => $kategori->getKey()])
            ->fillForm(['nama' => 'Kategori Diubah'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Kategori Diubah', $kategori->fresh()->nama);
    }

    #[Test]
    public function admin_bisa_membuat_dan_mengubah_satuan(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateSatuan::class)
            ->fillForm([
                'kode' => 'ROLL',
                'nama' => 'Roll',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $satuan = Satuan::query()->where('kode', 'ROLL')->firstOrFail();

        Livewire::test(EditSatuan::class, ['record' => $satuan->getKey()])
            ->fillForm(['nama' => 'Roll Besar'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Roll Besar', $satuan->fresh()->nama);
    }

    #[Test]
    public function admin_bisa_membuat_dan_mengubah_grade(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'kode' => 'B',
                'kategori_id' => $this->kategoriDty->id,
                'keterangan' => 'Grade B',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $grade = Grade::query()->where('kode', 'B')->where('kategori_id', $this->kategoriDty->id)->firstOrFail();

        Livewire::test(EditGrade::class, ['record' => $grade->getKey()])
            ->fillForm(['keterangan' => 'Grade B diubah'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Grade B diubah', $grade->fresh()->keterangan);
    }

    #[Test]
    public function admin_bisa_membuat_dan_mengubah_konversi_global(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateKonversiSatuan::class)
            ->fillForm([
                'barang_id' => null,
                'dari_satuan_id' => $this->satuanKg->id,
                'ke_satuan_id' => $this->satuanBox->id,
                'faktor' => 12.5,
                'is_default' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $konversi = KonversiSatuan::query()
            ->whereNull('barang_id')
            ->where('dari_satuan_id', $this->satuanKg->id)
            ->where('ke_satuan_id', $this->satuanBox->id)
            ->firstOrFail();

        $this->assertSame('12.5000', $konversi->faktor);

        Livewire::test(EditKonversiSatuan::class, ['record' => $konversi->getKey()])
            ->fillForm(['faktor' => 20])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('20.0000', $konversi->fresh()->faktor);
        $this->assertDatabaseHas('activity_logs', ['event' => 'CREATE', 'modul' => 'MASTER_KONVERSI']);
    }

    // ------------------------------------------------------------------
    // Validasi keunikan
    // ------------------------------------------------------------------

    #[Test]
    public function kode_partner_kategori_dan_satuan_harus_unik(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'kode' => $this->partner->kode,
                'nama' => 'Duplikat',
                'jenis' => 'SUPPLIER',
            ])
            ->call('create')
            ->assertHasFormErrors(['kode']);

        Livewire::test(CreateKategori::class)
            ->fillForm([
                'kode' => $this->kategoriDty->kode,
                'nama' => 'Duplikat',
            ])
            ->call('create')
            ->assertHasFormErrors(['kode']);

        Livewire::test(CreateSatuan::class)
            ->fillForm([
                'kode' => $this->satuanKg->kode,
                'nama' => 'Duplikat',
            ])
            ->call('create')
            ->assertHasFormErrors(['kode']);
    }

    #[Test]
    public function kode_grade_harus_unik_dalam_kategori_yang_sama(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateGrade::class)
            ->fillForm([
                'kode' => $this->gradeDtyA->kode,
                'kategori_id' => $this->kategoriDty->id,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['kode']);

        // Kode yang sama pada kategori berbeda tetap diperbolehkan.
        Livewire::test(CreateGrade::class)
            ->fillForm([
                'kode' => $this->gradeDtyA->kode,
                'kategori_id' => $this->kategoriPendukung->id,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    // ------------------------------------------------------------------
    // Proteksi Grade C DTY
    // ------------------------------------------------------------------

    #[Test]
    public function grade_c_dty_tidak_bisa_dinonaktifkan_atau_diubah_identitasnya(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(EditGrade::class, ['record' => $this->gradeCDty->getKey()])
            ->fillForm([
                'kode' => 'X',
                'kategori_id' => $this->kategoriPoy->id,
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $segar = $this->gradeCDty->fresh();

        $this->assertSame(Grade::KODE_SCRAP, $segar->kode);
        $this->assertSame($this->kategoriDty->id, $segar->kategori_id);
        $this->assertTrue($segar->is_active);
    }

    #[Test]
    public function grade_c_dty_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->admin);

        $this->assertNotNull(GradeResource::alasanTidakBisaDihapus($this->gradeCDty));

        Livewire::test(ListGrades::class)
            ->assertTableActionHidden('delete', $this->gradeCDty);
    }

    // ------------------------------------------------------------------
    // Validasi konversi
    // ------------------------------------------------------------------

    #[Test]
    public function konversi_untuk_barang_poy_ditolak(): void
    {
        $this->actingAs($this->admin);
        $poy = $this->barangPoy();

        Livewire::test(CreateKonversiSatuan::class)
            ->fillForm([
                'barang_id' => $poy->id,
                'dari_satuan_id' => $this->satuanBobbin->id,
                'ke_satuan_id' => $this->satuanKg->id,
                'faktor' => 16,
            ])
            ->call('create')
            ->assertHasFormErrors(['barang_id']);

        $this->assertDatabaseCount('konversi_satuan', 1);
    }

    #[Test]
    public function kombinasi_konversi_duplikat_ditolak_termasuk_global(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateKonversiSatuan::class)
            ->fillForm([
                'barang_id' => null,
                'dari_satuan_id' => $this->satuanBobbin->id,
                'ke_satuan_id' => $this->satuanBox->id,
                'faktor' => 99,
            ])
            ->call('create')
            ->assertHasFormErrors(['ke_satuan_id']);

        $barang = $this->barangDty();

        Livewire::test(CreateKonversiSatuan::class)
            ->fillForm([
                'barang_id' => $barang->id,
                'dari_satuan_id' => $this->satuanBobbin->id,
                'ke_satuan_id' => $this->satuanBox->id,
                'faktor' => 16,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateKonversiSatuan::class)
            ->fillForm([
                'barang_id' => $barang->id,
                'dari_satuan_id' => $this->satuanBobbin->id,
                'ke_satuan_id' => $this->satuanBox->id,
                'faktor' => 20,
            ])
            ->call('create')
            ->assertHasFormErrors(['ke_satuan_id']);
    }

    #[Test]
    public function satuan_asal_dan_tujuan_tidak_boleh_sama(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateKonversiSatuan::class)
            ->fillForm([
                'barang_id' => null,
                'dari_satuan_id' => $this->satuanKg->id,
                'ke_satuan_id' => $this->satuanKg->id,
                'faktor' => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['ke_satuan_id']);

        $this->assertDatabaseCount('konversi_satuan', 1);
    }

    // ------------------------------------------------------------------
    // Guard hapus data referensi
    // ------------------------------------------------------------------

    #[Test]
    public function kategori_yang_dipakai_barang_atau_grade_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListKategoris::class)
            ->callAction(TestAction::make('delete')->table($this->kategoriPoy));

        $this->assertDatabaseHas('kategori_barang', ['id' => $this->kategoriPoy->id]);

        $kategoriBaru = KategoriBarang::create(['kode' => 'EXTRA', 'nama' => 'Extra']);
        Barang::create([
            'kategori_id' => $kategoriBaru->id,
            'kode_barang' => 'EXTRA-1',
            'nama_barang' => 'Barang Extra',
            'satuan_dasar_id' => $this->satuanKg->id,
            'is_merge_minggu' => false,
            'is_active' => true,
        ]);

        Livewire::test(ListKategoris::class)
            ->callAction(TestAction::make('delete')->table($kategoriBaru->fresh()));

        $this->assertDatabaseHas('kategori_barang', ['id' => $kategoriBaru->id]);
    }

    #[Test]
    public function satuan_yang_dipakai_barang_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->admin);
        $this->barangPoy();

        Livewire::test(ListSatuans::class)
            ->callAction(TestAction::make('delete')->table($this->satuanKg));

        $this->assertDatabaseHas('satuan', ['id' => $this->satuanKg->id]);
    }

    #[Test]
    public function grade_yang_punya_stok_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->admin);
        $barang = $this->barangDty();

        StokBarang::create([
            'barang_id' => $barang->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'stok_aktif' => 10,
            'stok_buffer' => 0,
        ]);

        Livewire::test(ListGrades::class)
            ->callAction(TestAction::make('delete')->table($this->gradeDtyA));

        $this->assertDatabaseHas('grade', ['id' => $this->gradeDtyA->id]);
    }

    #[Test]
    public function admin_bisa_menghapus_konversi_dan_mencatat_activity_log(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListKonversiSatuans::class)
            ->callAction(TestAction::make('delete')->table($this->konversiGlobal));

        $this->assertDatabaseMissing('konversi_satuan', ['id' => $this->konversiGlobal->id]);
        $this->assertDatabaseHas('activity_logs', ['event' => 'DELETE', 'modul' => 'MASTER_KONVERSI']);
    }
}
