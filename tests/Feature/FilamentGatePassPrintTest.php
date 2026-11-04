<?php

namespace Tests\Feature;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Filament\Resources\PenerimaanPendukung\Pages\ListPenerimaanPendukung;
use App\Filament\Resources\PenerimaanPendukung\PenerimaanPendukungResource;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\PenerimaanService;
use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji halaman cetak Gate Pass versi Filament.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class FilamentGatePassPrintTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private Barang $pendukung;

    private Satuan $pcs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja();

        $this->admin = User::factory()->admin()->create();
        $this->pcs = Satuan::create(['kode' => Satuan::PCS, 'nama' => 'Pieces']);

        $this->pendukung = Barang::create([
            'kategori_id' => $this->kategoriPendukung->id,
            'kode_barang' => 'PPR TUB DTY',
            'nama_barang' => 'Paper Tube DTY Bullnose',
            'satuan_dasar_id' => $this->pcs->id,
            'is_merge_minggu' => false,
            'is_active' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function gate_pass_terbit_menampilkan_nomor_dan_batch(): void
    {
        $transaksi = $this->buatPendukung('27041797', 'GRN-VELVET');

        app(PenerimaanService::class)->posting($transaksi, $this->admin->id);
        $transaksi->refresh();

        $this->assertSame(StatusDokumen::Issued, $transaksi->status);

        $this->actingAs($this->admin)
            ->get(PenerimaanPendukungResource::getUrl('cetak', ['record' => $transaksi]))
            ->assertOk()
            ->assertSee('GATE PASS')
            ->assertSee('27041797')
            ->assertSee('GRN-VELVET');
    }

    #[Test]
    public function aksi_cetak_hanya_tampil_untuk_dokumen_terbit(): void
    {
        $terbit = $this->buatPendukung('27041799', 'GRN-CETAK');
        app(PenerimaanService::class)->posting($terbit, $this->admin->id);
        $terbit->refresh();

        $draf = $this->buatPendukung('27041800', 'GRN-DRAF-2');
        $void = $this->buatPendukung('27041801', 'GRN-VOID');
        $void->update(['status' => StatusDokumen::Void]);

        $this->actingAs($this->admin);

        Livewire::test(ListPenerimaanPendukung::class)
            ->assertActionVisible(TestAction::make('cetak')->table($terbit))
            ->assertActionHidden(TestAction::make('cetak')->table($draf))
            ->assertActionHidden(TestAction::make('cetak')->table($void));
    }

    #[Test]
    public function draf_gate_pass_tidak_bisa_dicetak(): void
    {
        $transaksi = $this->buatPendukung('27041798', 'GRN-DRAF');

        $this->assertSame(StatusDokumen::Draft, $transaksi->status);

        $this->actingAs($this->admin)
            ->get(PenerimaanPendukungResource::getUrl('cetak', ['record' => $transaksi]))
            ->assertForbidden();
    }

    #[Test]
    public function void_gate_pass_tidak_bisa_dicetak(): void
    {
        $transaksi = $this->buatPendukung('27041802', 'GRN-VOID-URL');
        $transaksi->update(['status' => StatusDokumen::Void]);

        $this->actingAs($this->admin)
            ->get(PenerimaanPendukungResource::getUrl('cetak', ['record' => $transaksi]))
            ->assertForbidden();
    }

    private function buatPendukung(string $nomorGatePass, string $batch): TransaksiMasuk
    {
        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-GP-'.uniqid(),
            'jenis_masuk' => JenisMasuk::PendukungGatepass,
            'nomor_gate_pass' => $nomorGatePass,
            'tanggal' => '2026-10-13',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->admin->id,
        ]);

        TransaksiMasukDetail::create([
            'transaksi_masuk_id' => $transaksi->id,
            'barang_id' => $this->pendukung->id,
            'merge' => 'BATCH-A',
            'grade_id' => $this->gradePendukungStd->id,
            'jumlah_kemasan' => 100,
            'satuan_kemasan_id' => $this->pcs->id,
            'berat_kg' => 100,
            'keterangan' => $batch,
        ]);

        return $transaksi;
    }
}
