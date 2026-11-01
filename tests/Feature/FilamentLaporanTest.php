<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\MutasiStok;
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
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji halaman laporan di panel Filament.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class FilamentLaporanTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    private Barang $poy;

    private Barang $dty;

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

        $this->ipci = SupplierCustomer::create(['kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'BOTH']);
        SupplierCustomer::create(['kode' => 'STORE-IPCI', 'nama' => 'Store IPCI', 'jenis' => 'SUPPLIER']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function semua_role_bisa_membuka_ketiga_halaman_laporan_filament(): void
    {
        foreach ([$this->admin, $this->operator, $this->manajer] as $user) {
            $this->actingAs($user)->get('/admin/laporan/keluar-masuk')->assertOk();
            $this->actingAs($user)->get('/admin/laporan/kartu-stok')->assertOk();
            $this->actingAs($user)->get('/admin/laporan/rendemen')->assertOk();
        }
    }

    #[Test]
    public function keluar_masuk_menampilkan_mutasi_posted_dan_total_serta_tidak_menampilkan_draft(): void
    {
        $posted = $this->buatMasukPoy('FLT-A', 1000, '2026-10-13');
        $this->buatMasukPoy('FLT-B', 250, '2026-10-14');
        $draf = $this->buatDrafMasukPoy('DRAFT-1', 500, '2026-10-13');

        $this->actingAs($this->operator)
            ->get('/admin/laporan/keluar-masuk?dari=2026-10-01&sampai=2026-10-31')
            ->assertOk()
            ->assertSee($posted->nomor_transaksi)
            ->assertSee('FLT-A')
            ->assertSee('FLT-B')
            ->assertSee('1.000,00')
            ->assertSee('250,00')
            ->assertSee('1.250,00')
            ->assertDontSee($draf->nomor_transaksi);
    }

    #[Test]
    public function filter_keluar_masuk_mempersempit_hasil(): void
    {
        $this->buatMasukPoy('FLT-A', 1000, '2026-10-13');
        $this->buatMasukPoy('FLT-B', 250, '2026-10-14');

        $this->actingAs($this->operator)
            ->get('/admin/laporan/keluar-masuk?dari=2026-10-01&sampai=2026-10-31&kategori=POY&arus=MASUK&merge=FLT-A')
            ->assertOk()
            ->assertSee('FLT-A')
            ->assertDontSee('FLT-B')
            ->assertSee('1.000,00')
            ->assertDontSee('250,00');
    }

    #[Test]
    public function kartu_stok_tanpa_filter_menampilkan_prompt(): void
    {
        $this->actingAs($this->operator)
            ->get('/admin/laporan/kartu-stok')
            ->assertOk()
            ->assertSee('Pilih barang dan isi merge untuk menampilkan kartu stok.');
    }

    #[Test]
    public function kartu_stok_menampilkan_saldo_awal_dan_saldo_berjalan(): void
    {
        $this->buatMasukPoy('M-KS-1', 800, '2026-09-20');
        $this->buatMasukPoy('M-KS-1', 1000, '2026-10-05');

        $this->actingAs($this->operator)
            ->get('/admin/laporan/kartu-stok?dari=2026-10-01&sampai=2026-10-31&barang_id='.$this->poy->id.'&merge=M-KS-1')
            ->assertOk()
            ->assertSee('Saldo awal')
            ->assertSee('800,00')
            ->assertSee('1.000,00')
            ->assertSee('1.800,00');
    }

    #[Test]
    public function kartu_stok_filter_pos_buffer_hanya_menampilkan_baris_buffer(): void
    {
        $aktif = $this->buatMasukPoy('M-KS-B', 1000, '2026-10-05');

        MutasiStok::create([
            'barang_id' => $this->poy->id,
            'merge' => 'M-KS-B',
            'grade_id' => $this->gradePoyAl->id,
            'tanggal' => '2026-10-06',
            'jenis_transaksi' => JenisMutasi::AlokasiBuffer,
            'pos' => PosStok::Buffer,
            'ref_nomor' => 'BUF-ROW',
            'masuk_qty' => 50,
            'keluar_qty' => 0,
            'saldo_akhir_qty' => 50,
        ]);

        $this->actingAs($this->operator)
            ->get('/admin/laporan/kartu-stok?dari=2026-10-01&sampai=2026-10-31&barang_id='.$this->poy->id.'&merge=M-KS-B&pos=BUFFER')
            ->assertOk()
            ->assertSee('BUF-ROW')
            ->assertSee('50,00')
            ->assertDontSee($aktif->nomor_transaksi);
    }

    #[Test]
    public function rendemen_menampilkan_total_persentase_baris_per_merge_dan_disclaimer(): void
    {
        $this->buatMasukPoy('MERGE-A', 1200, '2026-10-11');
        $keluar = $this->buatDrafKeluarPoy('MERGE-A', 1000, '2026-10-12');
        $this->app->make(PengeluaranService::class)->posting($keluar, $this->operator->id);
        $this->buatMasukDty('MERGE-A', 950, '2026-10-13');

        $this->actingAs($this->operator)
            ->get('/admin/laporan/rendemen?dari=2026-10-01&sampai=2026-10-31')
            ->assertOk()
            ->assertSee('Total POY Keluar')
            ->assertSee('Total DTY Masuk')
            ->assertSee('1.000,00')
            ->assertSee('950,00')
            ->assertSee('95,00%')
            ->assertSee('5,00%')
            ->assertSee('MERGE-A')
            ->assertSee((string) config('wms.rendemen.disclaimer_ringkas'))
            ->assertSee((string) config('wms.rendemen.disclaimer_lengkap'))
            ->assertSee('Selengkapnya');
    }

    #[Test]
    public function halaman_menyertakan_tautan_export_dengan_parameter_filter_saat_ini(): void
    {
        $this->actingAs($this->operator)
            ->get('/admin/laporan/keluar-masuk?dari=2026-10-01&sampai=2026-10-31&kategori=POY&arus=MASUK&merge=FLT-A')
            ->assertOk()
            ->assertSee(route('admin.laporan.keluar-masuk.excel', [
                'dari' => '2026-10-01',
                'sampai' => '2026-10-31',
                'kategori' => 'POY',
                'arus' => 'MASUK',
                'merge' => 'FLT-A',
            ]))
            ->assertSee(route('admin.laporan.keluar-masuk.pdf', [
                'dari' => '2026-10-01',
                'sampai' => '2026-10-31',
                'kategori' => 'POY',
                'arus' => 'MASUK',
                'merge' => 'FLT-A',
            ]));

        $this->actingAs($this->operator)
            ->get('/admin/laporan/kartu-stok?dari=2026-10-01&sampai=2026-10-31&barang_id='.$this->poy->id.'&merge=M-KS-1&grade_id='.$this->gradePoyAl->id.'&pos=BUFFER')
            ->assertOk()
            ->assertSee(route('admin.laporan.kartu-stok.excel', [
                'dari' => '2026-10-01',
                'sampai' => '2026-10-31',
                'barang_id' => $this->poy->id,
                'merge' => 'M-KS-1',
                'grade_id' => $this->gradePoyAl->id,
                'pos' => 'BUFFER',
            ]))
            ->assertSee(route('admin.laporan.kartu-stok.pdf', [
                'dari' => '2026-10-01',
                'sampai' => '2026-10-31',
                'barang_id' => $this->poy->id,
                'merge' => 'M-KS-1',
                'grade_id' => $this->gradePoyAl->id,
                'pos' => 'BUFFER',
            ]));

        $this->actingAs($this->operator)
            ->get('/admin/laporan/rendemen?dari=2026-10-01&sampai=2026-10-31')
            ->assertOk()
            ->assertSee(route('admin.laporan.rendemen.excel', [
                'dari' => '2026-10-01',
                'sampai' => '2026-10-31',
            ]))
            ->assertSee(route('admin.laporan.rendemen.pdf', [
                'dari' => '2026-10-01',
                'sampai' => '2026-10-31',
            ]));
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
