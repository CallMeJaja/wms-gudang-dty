<?php

namespace Tests\Feature;

use App\Enums\StatusDokumen;
use App\Models\AlokasiBuffer;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Services\LaporanService;
use App\Services\StokService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoTransaksiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uji seeder demo: guard local, idempoten, buffer & rendemen berisi.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class DemoTransaksiSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function master_barang_memuat_kode_material_dan_satuan_pcs(): void
    {
        $zona = (string) config('app.timezone');
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-13 10:00:00', $zona));

        $this->seed(DatabaseSeeder::class);

        $harapan = [
            'PPR TUB DTY, BULLNOSE' => ['5003301227', 'PCS'],
            'CARTON BOX DTY 675x444x278MM, KRAFT 200' => ['5000002512', 'PCS'],
            'PALLET DTY SMALL BOX 1370x1150x140MM' => ['5003301241', 'PCS'],
            'TAPE 250MX3IN, VISTAC TRANSPARENT' => ['5003301180', 'PCS'],
            'DTY PLASTIC BAG HDPE VRGN (4%A) 36/42x55 CM' => ['5000001861', 'KG'],
            'STRETCH FILM 12MC' => ['5003301185', 'PCS'],
            'DTY CONING OIL, TM368 BN' => ['1003000201', 'KG'],
        ];

        foreach ($harapan as $kodeBarang => [$kodeMaterial, $satuan]) {
            $barang = Barang::query()->with('satuanDasar')->where('kode_barang', $kodeBarang)->firstOrFail();

            $this->assertSame($kodeMaterial, $barang->kode_material);
            $this->assertSame($satuan, $barang->satuanDasar?->kode, "Satuan {$kodeBarang} salah.");
        }

        $this->assertNull(Barang::query()->where('kode_barang', 'POY-250-48')->value('kode_material'));
        $this->assertNull(Barang::query()->where('kode_barang', 'DTY-150-48-1')->value('kode_material'));
    }

    #[Test]
    public function demo_guard_idempoten_buffer_rendemen_draft(): void
    {
        $zona = (string) config('app.timezone');
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-13 10:00:00', $zona));

        $this->seed(DatabaseSeeder::class);
        $this->assertSame(0, TransaksiMasuk::where('nomor_transaksi', 'like', 'DEMO-%')->count());

        $this->seed(DemoTransaksiSeeder::class);
        $masuk = TransaksiMasuk::where('nomor_transaksi', 'like', 'DEMO-%')->count();
        $keluar = TransaksiKeluar::where('nomor_transaksi', 'like', 'DEMO-%')->count();

        $this->seed(DemoTransaksiSeeder::class);
        $this->assertSame($masuk, TransaksiMasuk::where('nomor_transaksi', 'like', 'DEMO-%')->count());
        $this->assertSame($keluar, TransaksiKeluar::where('nomor_transaksi', 'like', 'DEMO-%')->count());

        $this->assertGreaterThanOrEqual(100, $masuk + $keluar);
        $this->assertTrue(
            AlokasiBuffer::query()->where('dilewati', true)->exists(),
            'Jejak buffer dilewati tidak ditemukan pada data demo.'
        );
        $this->assertGreaterThanOrEqual(0, StokBarang::query()->min('stok_aktif') ?? 0);
        $this->assertSame(0, StokBarang::query()->where('stok_aktif', '<', 0)->count());

        $this->assertGreaterThan(0, $this->app->make(StokService::class)->totalBufferMinggu());

        $poy = Barang::query()->where('kode_barang', 'POY-250-48')->firstOrFail();
        $this->assertGreaterThan(
            0.0,
            (float) StokBarang::query()->where('barang_id', $poy->id)->sum('stok_aktif'),
            'Stok aktif POY harus tersedia untuk demo pengeluaran.'
        );

        $hasil = $this->app->make(LaporanService::class)->rendemen(
            now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()
        );
        $this->assertNotNull($hasil['rendemen']);
        $this->assertGreaterThanOrEqual(90.0, (float) $hasil['rendemen']);
        $this->assertLessThanOrEqual(105.0, (float) $hasil['rendemen']);

        $draf = TransaksiMasuk::where('nomor_transaksi', 'DEMO-POY-DRAFT-01')->firstOrFail();
        $this->assertSame(StatusDokumen::Draft, $draf->status);
        $this->assertSame(0, MutasiStok::where('ref_nomor', $draf->nomor_transaksi)->count());
        $this->assertSame(0, StokBarang::where('merge', '4P1066')->count());
    }
}
