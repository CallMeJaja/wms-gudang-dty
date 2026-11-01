<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Barangs\BarangResource;
use App\Filament\Resources\Kategoris\KategoriResource;
use App\Filament\Resources\KonversiSatuans\KonversiSatuanResource;
use App\Filament\Resources\PenerimaanDty\PenerimaanDtyResource;
use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use App\Filament\Resources\PenerimaanPoy\PenerimaanPoyResource;
use App\Filament\Resources\PengeluaranPoy\PengeluaranPoyResource;
use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\User;
use Filament\Enums\UserMenuPosition;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Branding panel, badge navigasi draf, dan pencarian global.
 */
class FilamentBrandingTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function panel_admin_mendaftarkan_enam_grup_navigasi_tanpa_ikon(): void
    {
        $groups = array_values(Filament::getPanel('admin')->getNavigationGroups());

        $this->assertCount(6, $groups);
        $this->assertSame(
            ['Data Induk', 'Penerimaan', 'Pengeluaran', 'Persediaan', 'Laporan', 'Pengaturan'],
            array_map(fn ($group): string|Htmlable => $group->getLabel(), $groups),
        );
        $this->assertSame(
            [null, null, null, null, null, null],
            array_map(fn ($group) => $group->getIcon(), $groups),
        );
    }

    #[Test]
    public function label_navigasi_memakai_istilah_yang_ringkas(): void
    {
        $this->assertSame('POY', PenerimaanPoyResource::getNavigationLabel());
        $this->assertSame('DTY', PenerimaanDtyResource::getNavigationLabel());
        $this->assertSame('POY', PengeluaranPoyResource::getNavigationLabel());
        $this->assertSame('Surat Jalan', SuratJalanDtyResource::getNavigationLabel());
        $this->assertSame('Kategori', KategoriResource::getNavigationLabel());
        $this->assertSame('Konversi', KonversiSatuanResource::getNavigationLabel());
        $this->assertSame('Gate Pass', PenerimaanPendukungResource::getNavigationLabel());
        $this->assertSame('Scrap / Reject', PengeluaranScrapResource::getNavigationLabel());
        $this->assertSame('Riwayat Aktivitas', ActivityLogResource::getNavigationLabel());
    }

    #[Test]
    public function dashboard_memakai_label_dan_judul_dashboard(): void
    {
        $dashboard = app(Dashboard::class);

        $this->assertSame('Beranda', Dashboard::getNavigationLabel());
        $this->assertSame('Beranda', $dashboard->getTitle());
    }

    #[Test]
    public function dashboard_admin_menampilkan_label_grup_navigasi(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get('/admin');

        $response->assertOk();

        foreach (['Data Induk', 'Penerimaan', 'Pengeluaran', 'Persediaan', 'Laporan', 'Pengaturan'] as $label) {
            $response->assertSee($label);
        }
    }

    #[Test]
    public function sidebar_tanpa_kontrol_ciutkan_dan_menu_pengguna_di_sidebar(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get('/admin');

        $response->assertOk();
        $response->assertDontSee('wms-sidebar-collapse');
        $response->assertDontSee('wms-sidebar-utility');

        $this->assertSame(
            UserMenuPosition::Sidebar,
            Filament::getPanel('admin')->getUserMenuPosition(),
        );
    }

    #[Test]
    public function panel_memuat_asset_gaya_sidebar(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get('/admin');

        $response->assertOk();
        $response->assertSee('wms-sidebar.css');
    }

    #[Test]
    public function sidebar_memuat_pencarian_menu_dan_pintasan_papan_tombol(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Cari menu')
            ->assertSee('wms-sidebar-search-kbd', false)
            ->assertSee('Ctrl K')
            ->assertDontSee('fi-global-search-ctn', false);
    }

    #[Test]
    public function sidebar_menampilkan_notifikasi_database(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('fi-sidebar-database-notifications-btn', false);
    }

    #[Test]
    public function badge_navigasi_penerimaan_poy_menghitung_draf(): void
    {
        $this->assertNull(PenerimaanPoyResource::getNavigationBadge());

        $this->buatDrafPenerimaan();
        $this->buatDrafPenerimaan();

        $this->assertSame('2', PenerimaanPoyResource::getNavigationBadge());

        $this->buatTransaksiMasuk([
            'nomor_transaksi' => 'UJI-POSTED',
            'status' => StatusDokumen::Posted,
        ]);

        $this->assertSame('2', PenerimaanPoyResource::getNavigationBadge());
    }

    #[Test]
    public function badge_navigasi_menghitung_draf_pada_semua_resource_transaksi(): void
    {
        $this->buatTransaksiMasuk([
            'nomor_transaksi' => 'UJI-POY',
            'jenis_masuk' => JenisMasuk::PoyIpci,
        ]);
        $this->buatTransaksiMasuk([
            'nomor_transaksi' => 'UJI-PENDUKUNG',
            'jenis_masuk' => JenisMasuk::PendukungGatepass,
        ]);
        $this->buatTransaksiMasuk([
            'nomor_transaksi' => 'UJI-DTY',
            'jenis_masuk' => JenisMasuk::DtyProduksi,
        ]);
        $this->buatTransaksiKeluar([
            'nomor_transaksi' => 'UJI-KELUAR-POY',
            'jenis_keluar' => JenisKeluar::PoyProduksi,
        ]);
        $this->buatTransaksiKeluar([
            'nomor_transaksi' => 'UJI-KELUAR-SJ',
            'jenis_keluar' => JenisKeluar::DtySuratJalan,
        ]);
        $this->buatTransaksiKeluar([
            'nomor_transaksi' => 'UJI-KELUAR-SCRAP',
            'jenis_keluar' => JenisKeluar::DtyScrapReject,
        ]);

        $this->assertSame('1', PenerimaanPoyResource::getNavigationBadge());
        $this->assertSame('1', PenerimaanPendukungResource::getNavigationBadge());
        $this->assertSame('1', PenerimaanDtyResource::getNavigationBadge());
        $this->assertSame('1', PengeluaranPoyResource::getNavigationBadge());
        $this->assertSame('1', SuratJalanDtyResource::getNavigationBadge());
        $this->assertSame('1', PengeluaranScrapResource::getNavigationBadge());
        $this->assertSame('warning', PenerimaanPoyResource::getNavigationBadgeColor());
    }

    #[Test]
    public function barang_punya_atribut_dan_url_pencarian_global(): void
    {
        $barang = $this->barangPoy();

        $attributes = BarangResource::getGloballySearchableAttributes();

        $this->assertContains('kode_barang', $attributes);
        $this->assertContains('nama_barang', $attributes);

        $url = BarangResource::getGlobalSearchResultUrl($barang);

        $this->assertNotNull($url);
        $this->assertStringContainsString('tableSearch='.$barang->kode_barang, $url);
    }

    #[Test]
    public function penerimaan_poy_punya_atribut_pencarian_global(): void
    {
        $this->assertContains('nomor_transaksi', PenerimaanPoyResource::getGloballySearchableAttributes());
        $this->assertContains('operator_nama', PenerimaanPoyResource::getGloballySearchableAttributes());
    }

    #[Test]
    public function berkas_brand_tersedia_di_public_images(): void
    {
        $this->assertFileExists(public_path('images/brand-logo.svg'));
        $this->assertFileExists(public_path('images/favicon.svg'));
    }

    #[Test]
    public function halaman_login_menampilkan_brand_logo(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('brand-logo.svg');
    }

    private function buatDrafPenerimaan(): TransaksiMasuk
    {
        return $this->buatTransaksiMasuk([
            'nomor_transaksi' => 'UJI-DRAF-'.uniqid(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $override
     */
    private function buatTransaksiMasuk(array $override = []): TransaksiMasuk
    {
        $partner = SupplierCustomer::firstOrCreate(
            ['kode' => 'UJI-SUP'],
            ['nama' => 'Supplier Uji', 'jenis' => 'SUPPLIER'],
        );

        return TransaksiMasuk::create(array_merge([
            'nomor_transaksi' => 'UJI-'.uniqid(),
            'jenis_masuk' => JenisMasuk::PoyIpci,
            'tanggal' => now()->toDateString(),
            'partner_id' => $partner->id,
            'status' => StatusDokumen::Draft,
            'created_by' => User::factory()->admin()->create()->id,
        ], $override));
    }

    /**
     * @param  array<string, mixed>  $override
     */
    private function buatTransaksiKeluar(array $override = []): TransaksiKeluar
    {
        $partner = SupplierCustomer::firstOrCreate(
            ['kode' => 'UJI-CUS'],
            ['nama' => 'Customer Uji', 'jenis' => 'CUSTOMER'],
        );

        return TransaksiKeluar::create(array_merge([
            'nomor_transaksi' => 'UJI-OUT-'.uniqid(),
            'jenis_keluar' => JenisKeluar::PoyProduksi,
            'tanggal' => now()->toDateString(),
            'partner_id' => $partner->id,
            'status' => StatusDokumen::Draft,
            'created_by' => User::factory()->admin()->create()->id,
        ], $override));
    }
}
