<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Filament\Resources\PengeluaranPoy\Pages\CreatePengeluaranPoy;
use App\Filament\Resources\PengeluaranPoy\Pages\ListPengeluaranPoy;
use App\Filament\Resources\PengeluaranPoy\PengeluaranPoyResource;
use App\Filament\Resources\PengeluaranScrap\Pages\CreatePengeluaranScrap;
use App\Filament\Resources\PengeluaranScrap\PengeluaranScrapResource;
use App\Filament\Resources\SuratJalanDty\Pages\CreateSuratJalanDty;
use App\Filament\Resources\SuratJalanDty\Pages\EditSuratJalanDty;
use App\Filament\Resources\SuratJalanDty\SuratJalanDtyResource;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\User;
use App\Services\NomorDokumenService;
use App\Services\PengeluaranService;
use App\Services\StokService;
use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji UI Filament Fase 2 (Transaksi Keluar).
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class FilamentPengeluaranTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    private Barang $poy;

    private Barang $dty;

    private SupplierCustomer $ipci;

    private Grade $gradeDtyC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja();

        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();

        $this->poy = $this->barangPoy(false);
        $this->dty = $this->barangDty();

        $this->gradeDtyC = Grade::create([
            'kode' => 'C',
            'kategori_id' => $this->kategoriDty->id,
            'keterangan' => 'DTY scrap',
            'is_active' => true,
        ]);

        $this->ipci = SupplierCustomer::create([
            'kode' => 'IPCI',
            'nama' => 'IPCI - 300097905',
            'jenis' => 'BOTH',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function admin_bisa_membuka_index_dan_create_tiga_resource(): void
    {
        $this->actingAs($this->admin);

        foreach ([
            PengeluaranPoyResource::getUrl(),
            SuratJalanDtyResource::getUrl(),
            PengeluaranScrapResource::getUrl(),
            PengeluaranPoyResource::getUrl('create'),
            SuratJalanDtyResource::getUrl('create'),
            PengeluaranScrapResource::getUrl('create'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    #[Test]
    public function operator_bisa_membuka_index_tiga_resource(): void
    {
        $this->actingAs($this->operator);

        $this->get(PengeluaranPoyResource::getUrl())->assertOk();
        $this->get(SuratJalanDtyResource::getUrl())->assertOk();
        $this->get(PengeluaranScrapResource::getUrl())->assertOk();
    }

    #[Test]
    public function manajer_hanya_bisa_melihat_index(): void
    {
        $this->actingAs($this->manajer);

        $this->get(PengeluaranPoyResource::getUrl())->assertOk();
        $this->get(SuratJalanDtyResource::getUrl())->assertOk();
        $this->get(PengeluaranScrapResource::getUrl())->assertOk();

        $this->get(PengeluaranPoyResource::getUrl('create'))->assertForbidden();
        $this->get(SuratJalanDtyResource::getUrl('create'))->assertForbidden();
        $this->get(PengeluaranScrapResource::getUrl('create'))->assertForbidden();

        $draf = $this->buatDrafPoy();

        $this->get(PengeluaranPoyResource::getUrl('edit', ['record' => $draf]))->assertForbidden();

        $this->assertTrue(Gate::forUser($this->manajer)->denies(
            'delete',
            [$draf, JenisKeluar::PoyProduksi],
        ));
    }

    #[Test]
    public function operator_dan_manajer_tidak_boleh_mengubah_atau_menghapus_transaksi_final(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $posted = $this->buatDrafPoy();
        app(PengeluaranService::class)->posting($posted, $this->admin->id);

        foreach ([$this->operator, $this->manajer] as $user) {
            $this->actingAs($user)
                ->get(PengeluaranPoyResource::getUrl('edit', ['record' => $posted->fresh()]))
                ->assertForbidden();

            $this->assertTrue(Gate::forUser($user)->denies(
                'delete',
                [$posted->fresh(), JenisKeluar::PoyProduksi],
            ));
        }
    }

    #[Test]
    public function livewire_create_poy_menyimpan_header_detail_dan_nomor_otomatis(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePengeluaranPoy::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 1,
                'operator_nama' => 'Budi Operator',
                'catatan' => 'Pengeluaran uji POY',
                'detail' => [
                    [
                        'barang_id' => $this->poy->id,
                        'merge' => '  M-POY-1  ',
                        'grade_id' => $this->gradePoyAl->id,
                        'jumlah_kemasan' => '3',
                        'jumlah_bobbin' => '12',
                        'berat_netto_kg' => '100',
                        'berat_gross_kg' => '',
                        'keterangan' => '  baris uji  ',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiKeluar::query()->sole();

        $this->assertStringStartsWith('OUT-', (string) $transaksi->nomor_transaksi);
        $this->assertSame(JenisKeluar::PoyProduksi, $transaksi->jenis_keluar);
        $this->assertSame(StatusDokumen::Draft, $transaksi->status);
        $this->assertSame($this->admin->id, $transaksi->created_by);
        $this->assertSame('Budi Operator', $transaksi->operator_nama);
        $this->assertSame('Pengeluaran uji POY', $transaksi->catatan);

        $detail = $transaksi->detail()->sole();

        $this->assertSame('M-POY-1', $detail->merge);
        $this->assertSame(3.0, (float) $detail->jumlah_kemasan);
        $this->assertNull($detail->berat_gross_kg);
        $this->assertNull($detail->satuan_kemasan_id);
        $this->assertSame(100.0, (float) $detail->berat_netto_kg);
        $this->assertSame('baris uji', $detail->keterangan);
        $this->assertSame(12, $detail->jumlah_bobbin);

        $this->assertSame(0, StokBarang::query()->count(), 'DRAFT tidak boleh membuat saldo stok.');
        $this->assertSame(0, MutasiStok::query()->count(), 'DRAFT tidak boleh menulis mutasi.');
    }

    #[Test]
    public function livewire_create_surat_jalan_lalu_issue_menyimpan_nomor_dan_mengurangi_stok(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $this->actingAs($this->admin);

        Livewire::test(CreateSuratJalanDty::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'partner_id' => $this->ipci->id,
                'no_kendaraan' => 'B 1234 CD',
                'nama_sopir' => 'Joko',
                'pic_pengirim' => 'R. Jamal',
                'pic_mengetahui' => 'Budi',
                'detail' => [
                    [
                        'barang_id' => $this->dty->id,
                        'merge' => '4D9160',
                        'grade_id' => $this->gradeDtyA->id,
                        'jumlah_kemasan' => 2,
                        'jumlah_bobbin' => 12,
                        'berat_netto_kg' => 66,
                        'berat_gross_kg' => 71.48,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiKeluar::query()->sole();

        $this->assertSame(JenisKeluar::DtySuratJalan, $transaksi->jenis_keluar);
        $this->assertSame(StatusDokumen::Draft, $transaksi->status);
        $this->assertNull($transaksi->nomor_surat_jalan, 'Nomor SJ tidak boleh digenerate saat create.');
        $this->assertSame($this->ipci->id, $transaksi->partner_id);
        $this->assertSame('B 1234 CD', $transaksi->no_kendaraan);
        $this->assertSame('Joko', $transaksi->nama_sopir);
        $this->assertSame('R. Jamal', $transaksi->pic_pengirim);
        $this->assertSame('Budi', $transaksi->pic_mengetahui);

        $detail = $transaksi->detail()->sole();
        $this->assertSame(71.48, (float) $detail->berat_gross_kg);
        $this->assertSame(66.0, (float) $detail->berat_netto_kg);

        Livewire::test(EditSuratJalanDty::class, ['record' => $transaksi->id])
            ->callAction('posting')
            ->assertHasNoActionErrors();

        $transaksi->refresh();

        $this->assertSame(StatusDokumen::Issued, $transaksi->status);
        $this->assertMatchesRegularExpression(
            '#^\d+/DTY/PEI/[IVX]+/\d{4}$#',
            (string) $transaksi->nomor_surat_jalan,
        );

        $saldo = StokBarang::query()->where('barang_id', $this->dty->id)->sole();
        $this->assertSame(934.0, (float) $saldo->stok_aktif);
    }

    #[Test]
    public function livewire_create_scrap_wajib_catatan_dan_yang_valid_tersimpan(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePengeluaranScrap::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'catatan' => '',
                'detail' => [
                    [
                        'barang_id' => $this->dty->id,
                        'merge' => '4D9160',
                        'grade_id' => $this->gradeDtyC->id,
                        'jumlah_bobbin' => 6,
                        'berat_netto_kg' => 100,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['catatan']);

        $this->assertSame(0, TransaksiKeluar::query()->count());

        Livewire::test(CreatePengeluaranScrap::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'catatan' => 'Dijual ke pengepul scrap.',
                'detail' => [
                    [
                        'barang_id' => $this->dty->id,
                        'merge' => '4D9160',
                        'grade_id' => $this->gradeDtyC->id,
                        'jumlah_bobbin' => 6,
                        'berat_netto_kg' => 100,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transaksi = TransaksiKeluar::query()->sole();

        $this->assertSame(JenisKeluar::DtyScrapReject, $transaksi->jenis_keluar);
        $this->assertSame(StatusDokumen::Draft, $transaksi->status);
        $this->assertSame('Dijual ke pengepul scrap.', $transaksi->catatan);
        $this->assertSame(100.0, (float) $transaksi->detail()->sole()->berat_netto_kg);
    }

    #[Test]
    public function posting_poy_mengurangi_stok_aktif_persis_dan_buffer_utuh(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000, 500);

        $transaksi = $this->buatDrafPoy();

        $this->actingAs($this->admin);

        Livewire::test(ListPengeluaranPoy::class)
            ->callAction(TestAction::make('posting')->table($transaksi))
            ->assertHasNoActionErrors();

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(600.0, (float) $saldo->stok_aktif);
        $this->assertSame(500.0, (float) $saldo->stok_buffer, 'Buffer tidak boleh tersentuh.');

        $mutasi = MutasiStok::query()
            ->where('jenis_transaksi', JenisMutasi::Keluar)
            ->where('ref_nomor', $transaksi->nomor_transaksi)
            ->sole();

        $this->assertSame(PosStok::Aktif, $mutasi->pos);
        $this->assertSame(400.0, (float) $mutasi->keluar_qty);
    }

    #[Test]
    public function posting_dengan_stok_aktif_tidak_cukup_ditolak_dan_stok_tetap(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 100, 5000);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 200]);

        $this->actingAs($this->admin);

        Livewire::test(ListPengeluaranPoy::class)
            ->callAction(TestAction::make('posting')->table($transaksi))
            ->assertNotified();

        $this->assertSame(StatusDokumen::Draft, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(100.0, (float) $saldo->stok_aktif);
        $this->assertSame(5000.0, (float) $saldo->stok_buffer);
    }

    #[Test]
    public function void_oleh_admin_mengembalikan_status_dan_stok(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $transaksi = $this->buatDrafPoy();
        app(PengeluaranService::class)->posting($transaksi, $this->operator->id);

        $this->actingAs($this->admin);

        Livewire::test(ListPengeluaranPoy::class)
            ->callAction(
                TestAction::make('void')->table($transaksi->fresh()),
                ['alasan_void' => 'Salah input berat timbangan produksi.'],
            )
            ->assertHasNoActionErrors();

        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(1000.0, (float) $saldo->stok_aktif);
    }

    #[Test]
    public function gross_lebih_kecil_dari_netto_ditolak(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePengeluaranPoy::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 1,
                'operator_nama' => 'Budi',
                'detail' => [
                    [
                        'barang_id' => $this->poy->id,
                        'merge' => 'M-POY-1',
                        'grade_id' => $this->gradePoyAl->id,
                        'berat_netto_kg' => 100,
                        'berat_gross_kg' => 50,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['detail.0.berat_gross_kg']);

        $this->assertSame(0, TransaksiKeluar::query()->count());
    }

    #[Test]
    public function barang_dan_grade_kategori_lain_ditolak(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateSuratJalanDty::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'no_kendaraan' => 'B 1234 CD',
                'nama_sopir' => 'Joko',
                'pic_pengirim' => 'R. Jamal',
                'pic_mengetahui' => 'Budi',
                'detail' => [
                    [
                        'barang_id' => $this->dty->id,
                        'merge' => '4D9160',
                        'grade_id' => $this->gradePoyAl->id,
                        'berat_netto_kg' => 10,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['detail.0.grade_id']);

        Livewire::test(CreatePengeluaranPoy::class)
            ->fillForm([
                'tanggal' => now()->toDateString(),
                'shift' => 1,
                'operator_nama' => 'Budi',
                'detail' => [
                    [
                        'barang_id' => $this->dty->id,
                        'merge' => '4D9160',
                        'grade_id' => $this->gradeDtyA->id,
                        'berat_netto_kg' => 10,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['detail.0.barang_id']);

        $this->assertSame(0, TransaksiKeluar::query()->count());
    }

    #[Test]
    public function record_jenis_lain_tidak_bisa_dibuka_lewat_resource_yang_salah(): void
    {
        $suratJalan = $this->buatDrafSj();
        $poy = $this->buatDrafPoy();

        $this->actingAs($this->admin);

        $this->get(PengeluaranPoyResource::getUrl('edit', ['record' => $suratJalan]))
            ->assertNotFound();

        $this->get(SuratJalanDtyResource::getUrl('edit', ['record' => $poy]))
            ->assertNotFound();

        $this->get(PengeluaranPoyResource::getUrl('edit', ['record' => $poy]))->assertOk();
        $this->get(SuratJalanDtyResource::getUrl('edit', ['record' => $suratJalan]))->assertOk();
    }

    #[Test]
    public function halaman_cetak_surat_jalan_terbit_menampilkan_nomor(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $transaksi = $this->buatDrafSj();
        app(PengeluaranService::class)->issue($transaksi, $this->admin->id);

        $transaksi->refresh();

        $this->actingAs($this->admin)
            ->get(SuratJalanDtyResource::getUrl('cetak', ['record' => $transaksi]))
            ->assertOk()
            ->assertSee('SURAT JALAN')
            ->assertSee((string) $transaksi->nomor_surat_jalan);
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu (pola sama dengan OutboundTest)
    |--------------------------------------------------------------------------
    */

    private function isiStok(Barang $barang, string $merge, int $gradeId, float $aktif, float $buffer = 0): void
    {
        $this->app->make(StokService::class)->tambahStok(
            $barang, $merge, $gradeId, $aktif + $buffer,
            PosStok::Aktif, JenisMutasi::Masuk,
            'STOK-AWAL-UJI', $this->admin->id,
        );

        if ($buffer > 0) {
            $this->app->make(StokService::class)->alokasiKeBuffer(
                $barang, $merge, $gradeId, $buffer, 'BUFFER-AWAL-UJI', $this->admin->id,
            );
        }

        MutasiStok::query()->delete();
    }

    /**
     * @param  array<string, mixed>  $ganti
     */
    private function buatDrafPoy(array $ganti = []): TransaksiKeluar
    {
        return $this->buatDraf(
            JenisKeluar::PoyProduksi,
            [
                'shift' => 1,
                'operator_nama' => 'Budi',
            ],
            [
                array_merge([
                    'barang_id' => $this->poy->id,
                    'merge' => 'M-POY-1',
                    'grade_id' => $this->gradePoyAl->id,
                    'jumlah_kemasan' => 1,
                    'jumlah_bobbin' => 12,
                    'berat_netto_kg' => 400,
                    'berat_gross_kg' => null,
                ], $ganti),
            ],
        );
    }

    private function buatDrafSj(): TransaksiKeluar
    {
        return $this->buatDraf(
            JenisKeluar::DtySuratJalan,
            [
                'partner_id' => $this->ipci->id,
                'no_kendaraan' => 'B 1234 CD',
                'nama_sopir' => 'Joko',
                'pic_pengirim' => 'R. Jamal',
                'pic_mengetahui' => 'Budi',
            ],
            [
                [
                    'barang_id' => $this->dty->id,
                    'merge' => '4D9160',
                    'grade_id' => $this->gradeDtyA->id,
                    'jumlah_kemasan' => 2,
                    'jumlah_bobbin' => 12,
                    'berat_netto_kg' => 66,
                    'berat_gross_kg' => 71.48,
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $detail
     */
    private function buatDraf(JenisKeluar $jenis, array $header, array $detail): TransaksiKeluar
    {
        $transaksi = TransaksiKeluar::create(array_merge([
            'nomor_transaksi' => app(NomorDokumenService::class)->generate('TRX_KELUAR'),
            'jenis_keluar' => $jenis,
            'tanggal' => now()->toDateString(),
            'status' => StatusDokumen::Draft,
            'created_by' => $this->admin->id,
        ], $header));

        foreach ($detail as $baris) {
            $transaksi->detail()->create($baris);
        }

        return $transaksi->refresh();
    }
}
