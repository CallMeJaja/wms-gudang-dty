<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\MutasiStok;
use App\Models\StokBarang;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\User;
use App\Services\PengeluaranService;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji modul transaksi keluar F3 (controller + policy + validasi + UI HTTP).
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class OutboundTest extends TestCase
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

        $this->ipci = SupplierCustomer::create(['kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'BOTH']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function operator_bisa_store_poy_draft_tanpa_mengurangi_stok(): void
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.poy.store'), $this->payloadPoy())
            ->assertRedirect();

        $transaksi = TransaksiKeluar::query()->sole();

        $this->assertSame(StatusDokumen::Draft, $transaksi->status);
        $this->assertSame(JenisKeluar::PoyProduksi, $transaksi->jenis_keluar);
        $this->assertSame(0, StokBarang::query()->count(), 'DRAFT tidak boleh membuat saldo stok.');
        $this->assertSame(0, MutasiStok::query()->count(), 'DRAFT tidak boleh menulis mutasi.');
    }

    #[Test]
    public function posting_poy_mengurangi_stok_aktif_persis(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000, 500);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 400]);

        $this->actingAs($this->operator)
            ->post(route('outbound.poy.posting', $transaksi))
            ->assertRedirect(route('outbound.poy.show', $transaksi))
            ->assertSessionHas('sukses');

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(600.0, (float) $saldo->stok_aktif);
        $this->assertSame(500.0, (float) $saldo->stok_buffer, 'Buffer tidak boleh tersentuh.');

        $mutasi = MutasiStok::query()->where('jenis_transaksi', JenisMutasi::Keluar)->sole();
        $this->assertSame(PosStok::Aktif, $mutasi->pos);
        $this->assertSame(400.0, (float) $mutasi->keluar_qty);
    }

    #[Test]
    public function posting_poy_melebihi_aktif_ditolak_dengan_pesan_a3(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 100, 5000);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 200]);

        $this->actingAs($this->operator)
            ->from(route('outbound.poy.show', $transaksi))
            ->post(route('outbound.poy.posting', $transaksi))
            ->assertSessionHasErrors('posting');

        $this->assertSame(
            AturanBisnisException::PESAN_BUFFER_TIDAK_BOLEH_DIPAKAI,
            session('errors')->first('posting'),
        );

        $this->assertSame(StatusDokumen::Draft, $transaksi->fresh()->status);
        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(100.0, (float) $saldo->stok_aktif);
        $this->assertSame(5000.0, (float) $saldo->stok_buffer);
    }

    #[Test]
    public function manajer_create_poy_ditolak_403(): void
    {
        $this->actingAs($this->manajer)
            ->get(route('outbound.poy.create'))
            ->assertForbidden();

        $this->actingAs($this->manajer)
            ->post(route('outbound.poy.store'), $this->payloadPoy())
            ->assertForbidden();
    }

    #[Test]
    public function issue_surat_jalan_generate_nomor_format_benar(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $transaksi = $this->buatDrafSj();

        $this->actingAs($this->operator)
            ->post(route('outbound.surat-jalan.issue', $transaksi))
            ->assertRedirect();

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Issued, $transaksi->status);
        $this->assertMatchesRegularExpression(
            '#^\d+/DTY/PEI/[IVX]+/\d{4}$#',
            (string) $transaksi->nomor_surat_jalan,
        );
    }

    #[Test]
    public function dua_surat_jalan_tanggal_sama_keduanya_issued_nomor_berbeda(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $pertama = $this->buatDrafSj();
        $kedua = $this->buatDrafSj();

        $this->assertSame(
            $pertama->tanggal->format('Y-m-d'),
            $kedua->tanggal->format('Y-m-d'),
            'Kedua SJ harus bertanggal sama.'
        );

        $this->actingAs($this->operator)->post(route('outbound.surat-jalan.issue', $pertama))->assertRedirect();
        $this->actingAs($this->operator)->post(route('outbound.surat-jalan.issue', $kedua))->assertRedirect();

        $pertama->refresh();
        $kedua->refresh();

        $this->assertSame(StatusDokumen::Issued, $pertama->status);
        $this->assertSame(StatusDokumen::Issued, $kedua->status);
        $this->assertNotSame($pertama->nomor_surat_jalan, $kedua->nomor_surat_jalan);
    }

    #[Test]
    public function surat_jalan_draft_tidak_mengurangi_stok(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $this->actingAs($this->operator)
            ->post(route('outbound.surat-jalan.store'), $this->payloadSj())
            ->assertRedirect();

        $saldo = StokBarang::query()->where('barang_id', $this->dty->id)->sole();
        $this->assertSame(1000.0, (float) $saldo->stok_aktif);
        $this->assertSame(0, MutasiStok::query()->count());
    }

    #[Test]
    public function scrap_tanpa_catatan_ditolak_validasi(): void
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.scrap.store'), $this->payloadScrap(['catatan' => '']))
            ->assertSessionHasErrors(['catatan']);

        $this->assertSame(0, TransaksiKeluar::query()->count());
    }

    #[Test]
    public function scrap_dengan_catatan_posting_mengurangi_stok(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyC->id, 500);

        $transaksi = $this->buatDrafScrap();

        $this->actingAs($this->operator)
            ->post(route('outbound.scrap.posting', $transaksi))
            ->assertRedirect(route('outbound.scrap.show', $transaksi));

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->dty->id)->sole();
        $this->assertSame(400.0, (float) $saldo->stok_aktif);
    }

    #[Test]
    public function shift_poy_wajib_dan_harus_1_2_3(): void
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.poy.store'), $this->payloadPoy([], ['shift' => null]))
            ->assertSessionHasErrors(['shift']);

        $this->actingAs($this->operator)
            ->post(route('outbound.poy.store'), $this->payloadPoy([], ['shift' => 5]))
            ->assertSessionHasErrors(['shift']);

        $this->assertSame(0, TransaksiKeluar::query()->count());
    }

    #[Test]
    public function void_poy_posted_oleh_admin_mengembalikan_stok(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 400]);
        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);

        $this->actingAs($this->admin)
            ->post(route('outbound.poy.void', $transaksi->fresh()), [
                'alasan_void' => 'Salah input berat timbangan produksi.',
            ])
            ->assertRedirect();

        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(1000.0, (float) $saldo->stok_aktif, 'Stok harus kembali utuh setelah void.');
    }

    #[Test]
    public function operator_void_ditolak_403(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 100]);
        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);

        $this->actingAs($this->operator)
            ->post(route('outbound.poy.void', $transaksi->fresh()), [
                'alasan_void' => 'Salah input berat timbangan produksi.',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function grade_kategori_lain_ditolak_di_detail(): void
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.poy.store'), $this->payloadPoy([
                'grade_id' => $this->gradeDtyA->id,
            ]))
            ->assertSessionHasErrors('detail.0.grade_id');

        $this->actingAs($this->operator)
            ->post(route('outbound.surat-jalan.store'), $this->payloadSj([
                'grade_id' => $this->gradePoyAl->id,
            ]))
            ->assertSessionHasErrors('detail.0.grade_id');

        $this->assertSame(0, TransaksiKeluar::query()->count());
    }

    #[Test]
    public function nomor_surat_jalan_tidak_bisa_duplikat_manual(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $this->actingAs($this->operator)
            ->post(route('outbound.surat-jalan.store'), $this->payloadSj([], ['nomor_surat_jalan' => '1/DTY/PEI/X/2026']))
            ->assertRedirect();

        $this->actingAs($this->operator)
            ->post(route('outbound.surat-jalan.store'), $this->payloadSj([], ['nomor_surat_jalan' => '1/DTY/PEI/X/2026']))
            ->assertSessionHasErrors(['nomor_surat_jalan']);

        $this->assertSame(1, TransaksiKeluar::query()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu
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
     * @param  array<string, mixed>  $gantiHeader
     * @param  array<string, mixed>  $gantiDetail
     * @return array<string, mixed>
     */
    private function payloadPoy(array $gantiDetail = [], array $gantiHeader = []): array
    {
        return array_merge([
            'tanggal' => '2026-10-13',
            'shift' => 1,
            'operator_nama' => 'Budi',
            'catatan' => null,
            'detail' => [
                array_merge([
                    'barang_id' => $this->poy->id,
                    'merge' => 'M-POY-1',
                    'grade_id' => $this->gradePoyAl->id,
                    'jumlah_kemasan' => '',
                    'jumlah_bobbin' => '',
                    'berat_netto_kg' => 100,
                    'berat_gross_kg' => '',
                ], $gantiDetail),
            ],
        ], $gantiHeader);
    }

    /**
     * @param  array<string, mixed>  $gantiHeader
     * @param  array<string, mixed>  $gantiDetail
     * @return array<string, mixed>
     */
    private function payloadSj(array $gantiDetail = [], array $gantiHeader = []): array
    {
        return array_merge([
            'tanggal' => '2026-10-13',
            'nomor_surat_jalan' => '',
            'partner_id' => $this->ipci->id,
            'no_kendaraan' => 'B 1234 CD',
            'nama_sopir' => 'Joko',
            'pic_pengirim' => 'R. Jamal',
            'pic_mengetahui' => 'Budi',
            'catatan' => null,
            'detail' => [
                array_merge([
                    'barang_id' => $this->dty->id,
                    'merge' => '4D9160',
                    'grade_id' => $this->gradeDtyA->id,
                    'jumlah_kemasan' => 2,
                    'jumlah_bobbin' => 12,
                    'berat_netto_kg' => 66,
                    'berat_gross_kg' => 71.48,
                ], $gantiDetail),
            ],
        ], $gantiHeader);
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function payloadScrap(array $ganti = []): array
    {
        return array_merge([
            'tanggal' => '2026-10-13',
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
        ], $ganti);
    }

    /**
     * @param  array<string, mixed>  $ganti
     */
    private function buatDrafPoy(array $ganti = []): TransaksiKeluar
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.poy.store'), $this->payloadPoy($ganti))
            ->assertSessionHasNoErrors();

        return TransaksiKeluar::query()->latest('id')->firstOrFail();
    }

    private function buatDrafSj(): TransaksiKeluar
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.surat-jalan.store'), $this->payloadSj())
            ->assertSessionHasNoErrors();

        return TransaksiKeluar::query()->latest('id')->firstOrFail();
    }

    private function buatDrafScrap(): TransaksiKeluar
    {
        $this->actingAs($this->operator)
            ->post(route('outbound.scrap.store'), $this->payloadScrap())
            ->assertSessionHasNoErrors();

        return TransaksiKeluar::query()->latest('id')->firstOrFail();
    }
}
