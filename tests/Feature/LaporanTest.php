<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiKeluarDetail;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\LaporanService;
use App\Services\PenerimaanService;
use App\Services\PengeluaranService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji matematika laporan F5 (PRD §6).
 *
 * UI legacy sudah dihapus; cakupan bisnis diuji lewat LaporanService.
 * UI Filament diuji oleh FilamentLaporanTest dan endpoint export oleh
 * LaporanExportTest.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class LaporanTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $operator;

    private Barang $poy;

    private Barang $dty;

    private SupplierCustomer $ipci;

    private LaporanService $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja();

        $this->operator = User::factory()->operator()->create();

        $this->poy = $this->barangPoy(true);
        $this->dty = $this->barangDty();

        $this->ipci = SupplierCustomer::create(['kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'BOTH']);
        SupplierCustomer::create(['kode' => 'STORE-IPCI', 'nama' => 'Store IPCI', 'jenis' => 'SUPPLIER']);

        $this->laporan = $this->app->make(LaporanService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function keluar_masuk_hanya_menampilkan_mutasi_yang_diposting(): void
    {
        $transaksi = $this->buatMasukPoy('M-POY-1', 1000, '2026-10-13');

        $baris = $this->laporan->barisKeluarMasuk([
            'dari' => '2026-10-01',
            'sampai' => '2026-10-31',
        ]);

        $this->assertCount(1, $baris);
        $this->assertSame($transaksi->nomor_transaksi, $baris->first()->ref_nomor);
        $this->assertSame('M-POY-1', $baris->first()->merge);
    }

    #[Test]
    public function draft_tidak_muncul_di_laporan_keluar_masuk(): void
    {
        $draf = $this->buatDrafMasukPoy('M-DRAF-1', 500, '2026-10-13');

        $this->assertSame(StatusDokumen::Draft, $draf->status);
        $this->assertSame(0, MutasiStok::query()->count());

        $baris = $this->laporan->barisKeluarMasuk([
            'dari' => '2026-10-01',
            'sampai' => '2026-10-31',
        ]);

        $this->assertCount(0, $baris);
    }

    #[Test]
    public function kartu_stok_saldo_awal_benar(): void
    {
        $this->buatMasukPoy('M-KS-1', 800, '2026-09-20');

        $hasil = $this->laporan->kartuStok(
            $this->poy->id, 'M-KS-1', null, null, '2026-10-01', '2026-10-31',
        );

        $this->assertSame(800.0, $hasil['saldoAwal']);
        $this->assertCount(0, $hasil['baris']);
    }

    #[Test]
    public function kartu_stok_saldo_berjalan_benar_per_baris(): void
    {
        $this->buatMasukPoy('M-KS-2', 1000, '2026-10-05');

        $keluar = $this->buatDrafKeluarPoy('M-KS-2', 400, '2026-10-10');
        $this->app->make(PengeluaranService::class)->posting($keluar, $this->operator->id);

        $hasil = $this->laporan->kartuStok(
            $this->poy->id, 'M-KS-2', null, null, '2026-10-01', '2026-10-31',
        );

        $this->assertCount(2, $hasil['baris']);
        $this->assertSame(1000.0, (float) $hasil['baris']->first()->saldo_berjalan);
        $this->assertSame(600.0, (float) $hasil['baris']->last()->saldo_berjalan);
    }

    #[Test]
    public function rendemen_angka_sesuai_rumus(): void
    {
        $this->buatMasukPoy('4P1065', 1200, '2026-10-11');
        $keluar = $this->buatDrafKeluarPoy('4P1065', 1000, '2026-10-12');
        $this->app->make(PengeluaranService::class)->posting($keluar, $this->operator->id);
        $this->buatMasukDty('4P1065', 950, '2026-10-13');

        $hasil = $this->laporan->rendemen('2026-10-01', '2026-10-31');

        $this->assertSame(1000.0, $hasil['totalPoy']);
        $this->assertSame(950.0, $hasil['totalDty']);
        $this->assertSame(95.0, $hasil['rendemen']);
        $this->assertSame(5.0, $hasil['waste']);
    }

    #[Test]
    public function rendemen_poy_nol_tanpa_error(): void
    {
        $hasil = $this->laporan->rendemen('2026-10-01', '2026-10-31');

        $this->assertSame(0.0, $hasil['totalPoy']);
        $this->assertNull($hasil['rendemen']);
        $this->assertNull($hasil['waste']);
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

        $hasil = $this->laporan->rendemen('2026-10-01', '2026-10-31');

        $mergeA = $hasil['perMerge']->firstWhere('merge', 'MERGE-A');
        $mergeB = $hasil['perMerge']->firstWhere('merge', 'MERGE-B');

        $this->assertNotNull($mergeA);
        $this->assertNotNull($mergeB);
        $this->assertSame(90.0, $mergeA['rendemen']);
        $this->assertSame(80.0, $mergeB['rendemen']);
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
}
