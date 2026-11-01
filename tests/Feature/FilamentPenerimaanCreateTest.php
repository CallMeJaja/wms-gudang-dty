<?php

namespace Tests\Feature;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Filament\Resources\PenerimaanDty\Pages\CreatePenerimaanDty;
use App\Filament\Resources\PenerimaanPendukung\Pages\CreatePenerimaanPendukung;
use App\Filament\Resources\PenerimaanPoy\Pages\CreatePenerimaanPoy;
use App\Filament\Resources\PenerimaanPoy\Pages\EditPenerimaanPoy;
use App\Filament\Resources\PenerimaanPoy\Pages\ListPenerimaanPoy;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\SupplierCustomer;
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
 * Uji alur simpan form Filament (bukan hanya halaman GET).
 *
 * Memastikan field virtual (Box/Gross/Batch) benar-benar dipetakan ke kolom
 * transaksi_masuk_detail, nomor transaksi digenerate, dan status awal DRAFT.
 */
class FilamentPenerimaanCreateTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private SupplierCustomer $ipci;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja();

        $this->admin = User::factory()->admin()->create();
        $this->ipci = SupplierCustomer::create([
            'kode' => 'IPCI',
            'nama' => 'IPCI - 300097905',
            'jenis' => 'SUPPLIER',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function create_poy_menyimpan_box_dan_nomor_otomatis(): void
    {
        $poy = $this->barangPoy();

        $this->actingAs($this->admin);

        Livewire::test(CreatePenerimaanPoy::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 1,
                'operator_nama' => 'Operator Uji',
                'partner_id' => $this->ipci->id,
                'detail' => [
                    [
                        'barang_id' => $poy->id,
                        'merge' => '4P1044',
                        'grade_id' => $this->gradePoyAl->id,
                        'jumlah_kemasan' => 7,
                        'jumlah_bobbin' => 252,
                        'berat_kg' => 4032,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiMasuk::query()
            ->where('jenis_masuk', JenisMasuk::PoyIpci->value)
            ->firstOrFail();

        $this->assertSame(StatusDokumen::Draft, $transaksi->status);
        $this->assertNotEmpty($transaksi->nomor_transaksi);
        $this->assertSame($this->admin->id, $transaksi->created_by);

        /** @var TransaksiMasukDetail $detail */
        $detail = $transaksi->detail()->firstOrFail();

        $this->assertSame($this->satuanBox->id, $detail->satuan_kemasan_id);
        $this->assertSame('4P1044', $detail->merge);
        $this->assertSame(4032.0, (float) $detail->berat_kg);
    }

    #[Test]
    public function aksi_posting_di_halaman_edit_menambah_stok(): void
    {
        $poy = $this->barangPoy();

        $this->actingAs($this->admin);

        Livewire::test(CreatePenerimaanPoy::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 1,
                'operator_nama' => 'Operator Uji',
                'partner_id' => $this->ipci->id,
                'detail' => [
                    [
                        'barang_id' => $poy->id,
                        'merge' => '4P1044',
                        'grade_id' => $this->gradePoyAl->id,
                        'jumlah_kemasan' => 7,
                        'jumlah_bobbin' => 252,
                        'berat_kg' => 4032,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiMasuk::query()->firstOrFail();

        Livewire::test(EditPenerimaanPoy::class, ['record' => $transaksi->id])
            ->callAction('posting')
            ->assertHasNoActionErrors();

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);
        $this->assertGreaterThan(0.0, (float) StokBarang::query()->sum('stok_aktif'));
    }

    #[Test]
    public function aksi_void_di_tabel_mengembalikan_status(): void
    {
        $poy = $this->barangPoy();

        $this->actingAs($this->admin);

        Livewire::test(CreatePenerimaanPoy::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 1,
                'operator_nama' => 'Operator Uji',
                'partner_id' => $this->ipci->id,
                'detail' => [
                    [
                        'barang_id' => $poy->id,
                        'merge' => '4P1044',
                        'grade_id' => $this->gradePoyAl->id,
                        'jumlah_kemasan' => 7,
                        'jumlah_bobbin' => 252,
                        'berat_kg' => 4032,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiMasuk::query()->firstOrFail();
        app(PenerimaanService::class)->posting($transaksi, $this->admin->id);

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);

        Livewire::test(ListPenerimaanPoy::class)
            ->callAction(
                TestAction::make('void')->table($transaksi),
                ['alasan_void' => 'Salah input berat timbangan'],
            )
            ->assertHasNoActionErrors();

        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);
    }

    #[Test]
    public function create_dty_menyimpan_gross_di_keterangan(): void
    {
        $dty = $this->barangDty();

        $this->actingAs($this->admin);

        Livewire::test(CreatePenerimaanDty::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 2,
                'operator_nama' => 'Operator DTY',
                'sumber_internal' => 'AREA PRODUKSI',
                'merge_poy_asal' => '4P1044',
                'detail' => [
                    [
                        'barang_id' => $dty->id,
                        'merge' => '4D9160',
                        'grade_id' => $this->gradeDtyA->id,
                        'jumlah_kemasan' => 2,
                        'jumlah_bobbin' => 12,
                        'berat_kg' => 66,
                        'berat_gross_kg' => 71.48,
                        'keterangan' => 'batch A',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiMasuk::query()
            ->where('jenis_masuk', JenisMasuk::DtyProduksi->value)
            ->firstOrFail();

        $this->assertSame('4P1044', $transaksi->merge_poy_asal);
        $this->assertSame('AREA PRODUKSI', $transaksi->sumber_internal);

        /** @var TransaksiMasukDetail $detail */
        $detail = $transaksi->detail()->firstOrFail();

        $this->assertSame($this->satuanBox->id, $detail->satuan_kemasan_id);
        $this->assertSame(66.0, (float) $detail->berat_kg);
        $this->assertSame(71.48, TransaksiMasukDetail::grossDariKeterangan($detail->keterangan));
        $this->assertStringContainsString('batch A', (string) $detail->keterangan);
    }

    #[Test]
    public function create_pendukung_menghitung_kg_dan_memakai_grade_std(): void
    {
        $pcs = Satuan::create(['kode' => Satuan::PCS, 'nama' => 'Pieces']);

        $barang = Barang::create([
            'kategori_id' => $this->kategoriPendukung->id,
            'kode_barang' => 'PPR TUB DTY',
            'nama_barang' => 'Paper Tube DTY',
            'satuan_dasar_id' => $pcs->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(CreatePenerimaanPendukung::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'partner_id' => $this->ipci->id,
                'detail' => [
                    [
                        'barang_id' => $barang->id,
                        'merge' => 'GRN-VELVET',
                        'jumlah_kemasan' => 10,
                        'satuan_kemasan_id' => $pcs->id,
                        'keterangan' => 'GRN-VELVET',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiMasuk::query()
            ->where('jenis_masuk', JenisMasuk::PendukungGatepass->value)
            ->firstOrFail();

        $this->assertNotEmpty($transaksi->nomor_gate_pass);
        $this->assertSame(StatusDokumen::Draft, $transaksi->status);

        /** @var TransaksiMasukDetail $detail */
        $detail = $transaksi->detail()->firstOrFail();

        $this->assertSame(10.0, (float) $detail->berat_kg);
        $this->assertSame($this->gradePendukungStd->id, $detail->grade_id);
        $this->assertSame($pcs->id, $detail->satuan_kemasan_id);
        $this->assertSame('GRN-VELVET', $detail->merge);
        $this->assertSame('GRN-VELVET', $detail->keterangan);
    }
}
