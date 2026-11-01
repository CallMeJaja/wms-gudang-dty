<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Exceptions\AturanBisnisException;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\StokOpname;
use App\Models\User;
use App\Services\StokOpnameService;
use App\Services\StokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji service stock opname (F6).
 *
 * UI/controller legacy sudah dihapus; cakupan bisnis dipertahankan dengan
 * memanggil StokOpnameService langsung. UI Filament diuji terpisah oleh
 * FilamentStokOpnameTest.
 */
class StokOpnameTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();

        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->barang = $this->barangDty('DTY-OPN-1');
    }

    private function stokAwal(float $qty): void
    {
        $this->app->make(StokService::class)->tambahStok(
            $this->barang, 'M1', $this->gradeDtyA->id, $qty,
        );
    }

    private function proses(float $stokFisik, string $tanggal = '2026-11-01', ?int $userId = null): StokOpname
    {
        return $this->app->make(StokOpnameService::class)->proses(
            $this->barang,
            'M1',
            $this->gradeDtyA->id,
            $stokFisik,
            $tanggal,
            $userId ?? $this->admin->id,
        );
    }

    #[Test]
    public function operator_tidak_boleh_proses_opname(): void
    {
        $this->stokAwal(100);

        $this->expectException(AturanBisnisException::class);

        $this->proses(130, userId: $this->operator->id);
    }

    #[Test]
    public function selisih_positif_buat_adjustment_in(): void
    {
        $this->stokAwal(100);

        $row = $this->proses(130);

        $this->assertNotNull($row->transaksi_masuk_id);
        $this->assertNull($row->transaksi_keluar_id);
        $this->assertSame(JenisMasuk::AdjustmentIn, $row->transaksiMasuk->jenis_masuk);
        $this->assertSame(30.0, (float) $row->transaksiMasuk->detail()->sole()->berat_kg);

        $saldo = $this->app->make(StokService::class)->saldo($this->barang, 'M1', $this->gradeDtyA->id);
        $this->assertSame(130.0, (float) $saldo->stok_aktif);
        $this->assertStringStartsWith('OPN-', $row->nomor_opname);
    }

    #[Test]
    public function selisih_negatif_buat_adjustment_out(): void
    {
        $this->stokAwal(100);

        $row = $this->proses(70);

        $this->assertNotNull($row->transaksi_keluar_id);
        $this->assertNull($row->transaksi_masuk_id);
        $this->assertSame(JenisKeluar::AdjustmentOut, $row->transaksiKeluar->jenis_keluar);

        $saldo = $this->app->make(StokService::class)->saldo($this->barang, 'M1', $this->gradeDtyA->id);
        $this->assertSame(70.0, (float) $saldo->stok_aktif);
    }

    #[Test]
    public function selisih_nol_tanpa_transaksi(): void
    {
        $this->stokAwal(100);

        $row = $this->proses(100);

        $this->assertNull($row->transaksi_masuk_id);
        $this->assertNull($row->transaksi_keluar_id);
    }

    #[Test]
    public function fisik_negatif_ditolak(): void
    {
        $this->stokAwal(100);

        $this->expectException(AturanBisnisException::class);

        $this->proses(-5);
    }

    #[Test]
    public function grade_kategori_lain_ditolak(): void
    {
        $this->stokAwal(100);

        $this->expectException(AturanBisnisException::class);

        $this->app->make(StokOpnameService::class)->proses(
            $this->barang,
            'M1',
            $this->gradePoyAl->id,
            100,
            '2026-11-01',
            $this->admin->id,
        );
    }

    #[Test]
    public function selisih_dihitung_db_bukan_input(): void
    {
        $this->stokAwal(100);

        $opname = $this->proses(125);

        $this->assertSame(25.0, (float) $opname->refresh()->selisih_qty);
        $this->assertNotContains('selisih_qty', (new StokOpname)->getFillable());
    }

    #[Test]
    public function recalculate_dry_run_tanpa_menulis(): void
    {
        $this->stokAwal(100);
        $target = MutasiStok::query()->latest('id')->firstOrFail();
        DB::table('mutasi_stok')->where('id', $target->id)->update(['saldo_akhir_qty' => 1]);

        $this->artisan('wms:recalculate-stock', ['--dry-run' => true])
            ->expectsOutputToContain('diperbaiki')
            ->assertSuccessful();

        $this->assertSame(1.0, (float) MutasiStok::findOrFail($target->id)->saldo_akhir_qty);
    }

    #[Test]
    public function recalculate_memperbaiki_snapshot_rusak(): void
    {
        $svc = $this->app->make(StokService::class);
        $svc->tambahStok($this->barang, 'M1', $this->gradeDtyA->id, 100);
        $svc->tambahStok($this->barang, 'M1', $this->gradeDtyA->id, 50);

        $target = MutasiStok::query()->orderBy('id')->firstOrFail();
        DB::table('mutasi_stok')->where('id', $target->id)->update(['saldo_akhir_qty' => 9999]);

        $this->artisan('wms:recalculate-stock')->assertSuccessful();

        $this->assertSame(100.0, (float) MutasiStok::findOrFail($target->id)->saldo_akhir_qty);
        $akhir = MutasiStok::query()->orderByDesc('id')->firstOrFail();
        $this->assertSame(150.0, (float) $akhir->saldo_akhir_qty);
    }

    #[Test]
    public function tanggal_sama_ditolak_tanggal_beda_diizinkan(): void
    {
        $svc = $this->app->make(StokOpnameService::class);
        $svc->proses($this->barang, 'M1', $this->gradeDtyA->id, 100, '2026-11-01', $this->admin->id);

        try {
            $svc->proses($this->barang, 'M1', $this->gradeDtyA->id, 100, '2026-11-01', $this->admin->id);
            $this->fail('Opname duplikat tanggal sama harus ditolak.');
        } catch (AturanBisnisException) {
            $this->assertTrue(true);
        }

        $kedua = $svc->proses($this->barang, 'M1', $this->gradeDtyA->id, 100, '2026-11-02', $this->admin->id);
        $this->assertSame('2026-11-02', $kedua->tanggal->toDateString());

        $stok = StokBarang::query()->posisi($this->barang->id, 'M1', $this->gradeDtyA->id)->firstOrFail();
        $this->assertSame(100.0, (float) $stok->stok_aktif);
    }
}
