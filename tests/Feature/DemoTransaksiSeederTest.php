<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use App\Events\StokBerubah;
use App\Models\AlokasiBuffer;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KonversiSatuan;
use App\Models\MutasiStok;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiKeluarDetail;
use App\Models\TransaksiMasuk;
use App\Models\User;
use App\Models\WasteLog;
use App\Services\LaporanService;
use App\Services\StokService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoTransaksiSeeder;
use Database\Seeders\NotifikasiDemoSeeder;
use Database\Seeders\WasteLogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
    public function master_barang_memuat_kode_numerik_grade_we_dan_konversi_poy(): void
    {
        $zona = (string) config('app.timezone');
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-13 10:00:00', $zona));

        $this->seed(DatabaseSeeder::class);

        foreach ([
            '5003301227',
            '5000002512',
            '5003301241',
            '5003301180',
            '5000001861',
            '5003301185',
            '1003000201',
        ] as $kodeBarang) {
            $this->assertDatabaseHas('barang', ['kode_barang' => $kodeBarang]);
        }

        $tape = Barang::query()->with('satuanDasar')->where('kode_barang', '5003301180')->firstOrFail();
        $stretch = Barang::query()->with('satuanDasar')->where('kode_barang', '5003301185')->firstOrFail();
        $poy = Barang::query()->where('kode_barang', 'POY-250-48')->firstOrFail();
        $dty = Barang::query()->where('kode_barang', 'DTY-150-48-1')->firstOrFail();

        $this->assertSame('PCS', $tape->satuanDasar?->kode);
        $this->assertSame('PCS', $stretch->satuanDasar?->kode);
        $this->assertTrue($poy->is_merge_minggu);
        $this->assertFalse($dty->is_merge_minggu);

        $this->assertTrue(
            Grade::query()
                ->where('kode', 'WE')
                ->where('kategori_id', $dty->kategori_id)
                ->where('is_active', true)
                ->exists()
        );

        $satuan = Satuan::pluck('id', 'kode');
        $faktor = fn (Barang $barang, string $dari, string $ke): float => (float) KonversiSatuan::query()
            ->where('barang_id', $barang->id)
            ->where('dari_satuan_id', $satuan[$dari])
            ->where('ke_satuan_id', $satuan[$ke])
            ->value('faktor');

        $this->assertSame(30.0, $faktor($dty, 'PALLET', 'BOX'));
        $this->assertSame(1.0, $faktor($poy, 'PALLET', 'BOX'));
        $this->assertSame(576.0, $faktor($poy, 'BOX', 'KG'));
        $this->assertSame(576.0, $faktor($poy, 'PALLET', 'KG'));
    }

    #[Test]
    public function demo_guard_idempoten_buffer_rendemen_draft(): void
    {
        $zona = (string) config('app.timezone');
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-13 10:00:00', $zona));

        Event::fake([StokBerubah::class]);

        $this->seed(DatabaseSeeder::class);
        $this->assertSame(0, TransaksiMasuk::where('nomor_transaksi', 'like', 'DEMO-%')->count());

        $this->seed(DemoTransaksiSeeder::class);
        $this->seed(WasteLogSeeder::class);
        $this->seed(NotifikasiDemoSeeder::class);
        $masuk = TransaksiMasuk::where('nomor_transaksi', 'like', 'DEMO-%')->count();
        $keluar = TransaksiKeluar::where('nomor_transaksi', 'like', 'DEMO-%')->count();

        $this->seed(DemoTransaksiSeeder::class);
        $this->seed(WasteLogSeeder::class);
        $this->seed(NotifikasiDemoSeeder::class);
        $this->assertSame($masuk, TransaksiMasuk::where('nomor_transaksi', 'like', 'DEMO-%')->count());
        $this->assertSame($keluar, TransaksiKeluar::where('nomor_transaksi', 'like', 'DEMO-%')->count());

        $this->assertGreaterThanOrEqual(100, $masuk + $keluar);

        $sj = TransaksiKeluar::query()
            ->where('jenis_keluar', JenisKeluar::DtySuratJalan)
            ->where('nomor_transaksi', 'like', 'DEMO-DTY-SJ-%')
            ->get();

        $this->assertCount(30, $sj);
        $this->assertSame(30, $sj->pluck('nomor_transaksi')->unique()->count());

        $detailSj = TransaksiKeluarDetail::query()
            ->whereIn('transaksi_keluar_id', $sj->pluck('id'))
            ->get();

        $this->assertGreaterThanOrEqual(30, $detailSj->count());

        foreach ($sj as $transaksi) {
            $detail = $detailSj->where('transaksi_keluar_id', $transaksi->id);

            $this->assertSame(100, (int) $detail->sum('jumlah_kemasan'));
            $this->assertSame(3300.0, round((float) $detail->sum('berat_netto_kg'), 2));
        }

        $this->assertSame(99000.0, round((float) $detailSj->sum('berat_netto_kg'), 2));

        $this->assertSame(
            0,
            TransaksiKeluar::query()->where('jenis_keluar', JenisKeluar::DtyScrapReject)->count(),
            'Demo tidak boleh membuat transaksi scrap lama.'
        );
        $this->assertGreaterThanOrEqual(4, WasteLog::query()->count());
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

        $akhir = now()->startOfDay();
        $awal = $akhir->copy()->subDays(29)->startOfDay();

        $hasil = $this->app->make(LaporanService::class)->rendemen(
            $awal->toDateString(), $akhir->toDateString()
        );
        $this->assertSame(99000.0, $hasil['totalDty']);
        $this->assertSame(106560.0, $hasil['totalPoy']);
        $this->assertSame(92.91, $hasil['rendemen']);
        $this->assertGreaterThanOrEqual(90.0, (float) $hasil['rendemen']);
        $this->assertLessThanOrEqual(100.0, (float) $hasil['rendemen']);
        $this->assertSame(100000.0, $hasil['targetKg']);

        $oil = Barang::query()->where('kode_barang', '1003000201')->firstOrFail();
        $this->assertSame(
            195.0,
            (float) StokBarang::query()
                ->where('barang_id', $oil->id)
                ->where('merge', '-')
                ->sum('stok_aktif')
        );

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $manajer = User::query()->where('username', 'manajer')->firstOrFail();

        $this->assertSame(2, DB::table('notifications')->count());
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $admin->id)->count());
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $manajer->id)->count());
        $this->assertSame(
            2,
            DB::table('notifications')->where('data->title', 'Stok 1003000201 menipis')->count()
        );

        $draf = TransaksiMasuk::where('nomor_transaksi', 'DEMO-POY-DRAFT-01')->firstOrFail();
        $this->assertSame(StatusDokumen::Draft, $draf->status);
        $this->assertSame(0, MutasiStok::where('ref_nomor', $draf->nomor_transaksi)->count());
        $this->assertSame(0, StokBarang::where('merge', '4P1066')->count());
    }
}
