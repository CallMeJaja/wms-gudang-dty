<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\User;
use App\Services\StokMenipisService;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

class StokMenipisServiceTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private StokMenipisService $service;

    private User $admin;

    private User $manajer;

    private User $operator;

    private User $adminNonaktif;

    private Satuan $satuanPcs;

    private Barang $poy;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Cache::flush();

        $this->siapkanMasterDasar();
        $this->service = $this->app->make(StokMenipisService::class);
        $this->satuanPcs = Satuan::create(['kode' => Satuan::PCS, 'nama' => 'Pieces']);

        $this->admin = User::factory()->admin()->create();
        $this->manajer = User::factory()->manajer()->create();
        $this->operator = User::factory()->operator()->create();
        $this->adminNonaktif = User::factory()->admin()->create(['is_active' => false]);

        $this->poy = $this->barangPoy();
        $this->aturStok($this->poy, $this->gradePoyAl->id, $this->ambangPoyKg());
    }

    #[Test]
    public function item_di_bawah_minimum_mengirim_notifikasi_ke_admin_dan_manajer_aktif_saja(): void
    {
        $barang = $this->barangPendukung('PAPER-TUBE', 100.0);
        $this->aturStok($barang, $this->gradePendukungStd->id, 50.0);

        $this->service->periksa();

        Notification::assertSentTo($this->admin, DatabaseNotification::class, function (DatabaseNotification $notification): bool {
            return $notification->data['title'] === 'Stok PAPER-TUBE menipis'
                && $notification->data['body'] === 'Sisa 50,00 PCS dari minimum 100,00 PCS.';
        });
        Notification::assertSentTo($this->manajer, DatabaseNotification::class);
        Notification::assertNotSentTo($this->operator, DatabaseNotification::class);
        Notification::assertNotSentTo($this->adminNonaktif, DatabaseNotification::class);
    }

    #[Test]
    public function pemeriksaan_berulang_tidak_menggandakan_notifikasi_saat_stok_masih_menipis(): void
    {
        $barang = $this->barangPendukung();
        $this->aturStok($barang, $this->gradePendukungStd->id, 50.0);

        $this->service->periksa();
        $this->service->periksa();

        Notification::assertSentToTimes($this->admin, DatabaseNotification::class, 1);
        Notification::assertSentToTimes($this->manajer, DatabaseNotification::class, 1);
    }

    #[Test]
    public function stok_aman_tidak_mengirim_notifikasi(): void
    {
        $barang = $this->barangPendukung();
        $this->aturStok($barang, $this->gradePendukungStd->id, 100.0);

        $this->service->periksa();

        Notification::assertNothingSent();
    }

    #[Test]
    public function stok_yang_pulih_lalu_menipis_lagi_mengirim_notifikasi_baru(): void
    {
        $barang = $this->barangPendukung();
        $this->aturStok($barang, $this->gradePendukungStd->id, 50.0);

        $this->service->periksa();

        $this->aturStok($barang, $this->gradePendukungStd->id, 150.0);
        $this->service->periksa();

        $this->aturStok($barang, $this->gradePendukungStd->id, 50.0);
        $this->service->periksa();

        Notification::assertSentToTimes($this->admin, DatabaseNotification::class, 2);
        Notification::assertSentToTimes($this->manajer, DatabaseNotification::class, 2);
    }

    #[Test]
    public function stok_aktif_poy_di_bawah_enam_pallet_mengirim_notifikasi_kritis(): void
    {
        $this->aturStok($this->poy, $this->gradePoyAl->id, 1000.0);

        $this->service->periksa();

        $pallet = (int) config('wms.ambang_poy.min_stok_aktif_pallet');
        $ambang = $pallet * (float) config('wms.pallet.poy_kg');
        $body = sprintf(
            'Stok aktif %s kg di bawah kebutuhan %s kg (%d pallet).',
            number_format(1000.0, 2, ',', '.'),
            number_format($ambang, 2, ',', '.'),
            $pallet,
        );

        Notification::assertSentTo($this->admin, DatabaseNotification::class, function (DatabaseNotification $notification) use ($body): bool {
            return $notification->data['title'] === 'Stok aktif POY kritis'
                && $notification->data['body'] === $body
                && $notification->data['status'] === 'danger';
        });
        Notification::assertSentTo($this->manajer, DatabaseNotification::class);
    }

    #[Test]
    public function command_notifikasi_stok_menipis_berhasil_dan_menjalankan_pemeriksaan(): void
    {
        $barang = $this->barangPendukung();
        $this->aturStok($barang, $this->gradePendukungStd->id, 50.0);

        $this->artisan('wms:notifikasi-stok-menipis')
            ->expectsOutput('Pemeriksaan notifikasi stok menipis selesai.')
            ->assertSuccessful();

        Notification::assertSentTo($this->admin, DatabaseNotification::class);
        Notification::assertSentTo($this->manajer, DatabaseNotification::class);
    }

    private function barangPendukung(string $kode = 'PAPER-TUBE', float $minimum = 100.0): Barang
    {
        return Barang::create([
            'kategori_id' => $this->kategoriPendukung->id,
            'kode_barang' => $kode,
            'nama_barang' => $kode,
            'satuan_dasar_id' => $this->satuanPcs->id,
            'stok_minimum' => $minimum,
            'is_merge_minggu' => false,
            'is_active' => true,
        ]);
    }

    private function aturStok(Barang $barang, int $gradeId, float $aktif, float $buffer = 0.0): StokBarang
    {
        return StokBarang::updateOrCreate(
            [
                'barang_id' => $barang->id,
                'merge' => 'M-1',
                'grade_id' => $gradeId,
            ],
            [
                'stok_aktif' => $aktif,
                'stok_buffer' => $buffer,
            ],
        );
    }

    private function ambangPoyKg(): float
    {
        return (int) config('wms.ambang_poy.min_stok_aktif_pallet') * (float) config('wms.pallet.poy_kg');
    }
}
