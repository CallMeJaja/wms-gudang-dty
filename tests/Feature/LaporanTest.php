<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\Satuan;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiKeluarDetail;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\PenerimaanService;
use App\Services\PengeluaranService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji laporan & cetak F5 (PRD §6).
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class LaporanTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    private Barang $poy;

    private Barang $dty;

    private Barang $pendukung;

    private SupplierCustomer $ipci;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja();

        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();

        $this->poy = $this->barangPoy(true);
        $this->dty = $this->barangDty();

        $pcs = Satuan::create(['kode' => Satuan::PCS, 'nama' => 'Pieces']);

        $this->pendukung = Barang::create([
            'kategori_id' => $this->kategoriPendukung->id,
            'kode_barang' => 'PPR TUB DTY',
            'nama_barang' => 'Paper Tube DTY Bullnose',
            'satuan_dasar_id' => $pcs->id,
            'is_merge_minggu' => false,
            'is_active' => true,
        ]);

        $this->ipci = SupplierCustomer::create(['kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'BOTH']);
        SupplierCustomer::create(['kode' => 'STORE-IPCI', 'nama' => 'Store IPCI', 'jenis' => 'SUPPLIER']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function tamu_diarahkan_ke_login_untuk_semua_halaman_laporan(): void
    {
        $this->get(route('laporan.keluar-masuk.index'))->assertRedirect(route('login'));
        $this->get(route('laporan.kartu-stok.index'))->assertRedirect(route('login'));
        $this->get(route('laporan.rendemen.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function manajer_bisa_membuka_ketiga_laporan(): void
    {
        $this->actingAs($this->manajer)->get(route('laporan.keluar-masuk.index'))->assertOk();
        $this->actingAs($this->manajer)->get(route('laporan.kartu-stok.index'))->assertOk();
        $this->actingAs($this->manajer)->get(route('laporan.rendemen.index'))->assertOk();
    }

    #[Test]
    public function keluar_masuk_menampilkan_mutasi_yang_diposting(): void
    {
        $transaksi = $this->buatMasukPoy('M-POY-1', 1000, '2026-10-13');

        $this->actingAs($this->operator)
            ->get(route('laporan.keluar-masuk.index', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertSee($transaksi->nomor_transaksi)
            ->assertSee('M-POY-1');
    }

    #[Test]
    public function draft_tidak_muncul_di_laporan_keluar_masuk(): void
    {
        $draf = $this->buatDrafMasukPoy('M-DRAF-1', 500, '2026-10-13');

        $this->assertSame(StatusDokumen::Draft, $draf->status);
        $this->assertSame(0, MutasiStok::query()->count());

        $this->actingAs($this->operator)
            ->get(route('laporan.keluar-masuk.index', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertDontSee($draf->nomor_transaksi);
    }

    #[Test]
    public function kartu_stok_saldo_awal_benar(): void
    {
        $this->buatMasukPoy('M-KS-1', 800, '2026-09-20');

        $this->actingAs($this->operator)
            ->get(route('laporan.kartu-stok.index', [
                'barang_id' => $this->poy->id, 'merge' => 'M-KS-1',
                'dari' => '2026-10-01', 'sampai' => '2026-10-31',
            ]))
            ->assertOk()
            ->assertSee('800,00');
    }

    #[Test]
    public function kartu_stok_saldo_berjalan_benar_per_baris(): void
    {
        $this->buatMasukPoy('M-KS-2', 1000, '2026-10-05');

        $keluar = $this->buatDrafKeluarPoy('M-KS-2', 400, '2026-10-10');
        $this->app->make(PengeluaranService::class)->posting($keluar, $this->operator->id);

        $this->actingAs($this->operator)
            ->get(route('laporan.kartu-stok.index', [
                'barang_id' => $this->poy->id, 'merge' => 'M-KS-2',
                'dari' => '2026-10-01', 'sampai' => '2026-10-31',
            ]))
            ->assertOk()
            ->assertSee('1.000,00')
            ->assertSee('600,00');
    }

    #[Test]
    public function rendemen_angka_sesuai_rumus(): void
    {
        $this->buatMasukPoy('4P1065', 1200, '2026-10-11');
        $keluar = $this->buatDrafKeluarPoy('4P1065', 1000, '2026-10-12');
        $this->app->make(PengeluaranService::class)->posting($keluar, $this->operator->id);
        $this->buatMasukDty('4P1065', 950, '2026-10-13');

        $this->actingAs($this->operator)
            ->get(route('laporan.rendemen.index', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertSee('95,00%')
            ->assertSee('5,00%');
    }

    #[Test]
    public function rendemen_menampilkan_disclaimer_otomatis(): void
    {
        $this->actingAs($this->operator)
            ->get(route('laporan.rendemen.index'))
            ->assertOk()
            ->assertSee((string) config('wms.rendemen.disclaimer'));
    }

    #[Test]
    public function rendemen_poy_nol_tampil_strip_tanpa_error(): void
    {
        $this->actingAs($this->operator)
            ->get(route('laporan.rendemen.index', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertSee('-');
    }

    #[Test]
    public function rendemen_per_merge_poy_asal_terpisah(): void
    {
        $this->buatMasukPoy('MERGE-A', 1200, '2026-10-11');
        $this->buatMasukPoy('MERGE-B', 800, '2026-10-11');
        $k1 = $this->buatDrafKeluarPoy('MERGE-A', 1000, '2026-10-12');
        $this->app->make(PengeluaranService::class)->posting($k1, $this->operator->id);
        $k2 = $this->buatDrafKeluarPoy('MERGE-B', 500, '2026-10-12');
        $this->app->make(PengeluaranService::class)->posting($k2, $this->operator->id);
        $this->buatMasukDty('MERGE-A', 900, '2026-10-13');
        $this->buatMasukDty('MERGE-B', 400, '2026-10-13');

        $this->actingAs($this->operator)
            ->get(route('laporan.rendemen.index', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertOk()
            ->assertSee('MERGE-A')
            ->assertSee('MERGE-B')
            ->assertSee('90,00%')
            ->assertSee('80,00%');
    }

    #[Test]
    public function export_excel_keluar_masuk_mengembalikan_xlsx(): void
    {
        $this->buatMasukPoy('M-XL-1', 100, '2026-10-13');

        $respon = $this->actingAs($this->operator)
            ->get(route('laporan.keluar-masuk.excel', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']));

        $respon->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml', (string) $respon->headers->get('Content-Type'),
            'Export Excel harus berformat xlsx.'
        );
        $dasar = $respon->baseResponse;
        $this->assertInstanceOf(BinaryFileResponse::class, $dasar);
        $this->assertGreaterThan(0, $dasar->getFile()->getSize());
    }

    #[Test]
    public function export_pdf_rendemen_mengembalikan_pdf(): void
    {
        $respon = $this->actingAs($this->operator)
            ->get(route('laporan.rendemen.pdf', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']));

        $respon->assertOk();
        $this->assertStringContainsString(
            'pdf', strtolower((string) $respon->headers->get('Content-Type')),
            'Export PDF harus berformat pdf.'
        );
        $this->assertGreaterThan(0, strlen((string) $respon->getContent()));
    }

    #[Test]
    public function cetak_gate_pass_memuat_nomor_dokumen(): void
    {
        $transaksi = $this->buatMasukPendukung();

        $this->actingAs($this->operator)
            ->get(route('inbound.pendukung.cetak', $transaksi))
            ->assertOk()
            ->assertSee($transaksi->nomor_transaksi)
            ->assertSee('GATE PASS');
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu
    |--------------------------------------------------------------------------
    */

    private function buatDrafMasukPoy(string $merge, float $berat, string $tanggal): TransaksiMasuk
    {
        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-TEST-'.$merge.'-'.uniqid(),
            'jenis_masuk' => JenisMasuk::PoyIpci,
            'tanggal' => $tanggal,
            'partner_id' => $this->ipci->id,
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        TransaksiMasukDetail::create([
            'transaksi_masuk_id' => $transaksi->id,
            'barang_id' => $this->poy->id,
            'merge' => $merge,
            'grade_id' => $this->gradePoyAl->id,
            'berat_kg' => $berat,
        ]);

        return $transaksi;
    }

    private function buatMasukPoy(string $merge, float $berat, string $tanggal): TransaksiMasuk
    {
        $transaksi = $this->buatDrafMasukPoy($merge, $berat, $tanggal);
        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        return $transaksi->fresh();
    }

    private function buatDrafKeluarPoy(string $merge, float $berat, string $tanggal): TransaksiKeluar
    {
        $transaksi = TransaksiKeluar::create([
            'nomor_transaksi' => 'OUT-TEST-'.$merge.'-'.uniqid(),
            'jenis_keluar' => JenisKeluar::PoyProduksi,
            'tanggal' => $tanggal,
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        TransaksiKeluarDetail::create([
            'transaksi_keluar_id' => $transaksi->id,
            'barang_id' => $this->poy->id,
            'merge' => $merge,
            'grade_id' => $this->gradePoyAl->id,
            'berat_netto_kg' => $berat,
        ]);

        return $transaksi;
    }

    private function buatMasukDty(string $mergePoyAsal, float $berat, string $tanggal): TransaksiMasuk
    {
        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-DTY-'.$mergePoyAsal.'-'.uniqid(),
            'jenis_masuk' => JenisMasuk::DtyProduksi,
            'tanggal' => $tanggal,
            'merge_poy_asal' => $mergePoyAsal,
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        TransaksiMasukDetail::create([
            'transaksi_masuk_id' => $transaksi->id,
            'barang_id' => $this->dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => 2,
            'berat_kg' => $berat,
        ]);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        return $transaksi->fresh();
    }

    private function buatMasukPendukung(): TransaksiMasuk
    {
        $pcs = Satuan::query()->where('kode', Satuan::PCS)->value('id');

        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-GP-'.uniqid(),
            'jenis_masuk' => JenisMasuk::PendukungGatepass,
            'nomor_gate_pass' => 'GP-'.uniqid(),
            'tanggal' => '2026-10-13',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        TransaksiMasukDetail::create([
            'transaksi_masuk_id' => $transaksi->id,
            'barang_id' => $this->pendukung->id,
            'merge' => 'BATCH-A',
            'grade_id' => $this->gradePendukungStd->id,
            'jumlah_kemasan' => 100,
            'satuan_kemasan_id' => $pcs,
            'berat_kg' => 100,
            'keterangan' => 'GRN-VELVET',
        ]);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        return $transaksi->fresh();
    }
}
