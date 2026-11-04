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
use App\Services\VoidTransaksiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji service transaksi keluar: posting/issue, policy, dan void.
 *
 * UI legacy sudah dihapus; cakupan bisnis diuji lewat
 * PengeluaranService/VoidTransaksiService dan Gate policy langsung.
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
    public function posting_poy_mengurangi_stok_aktif_persis(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000, 500);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 400]);

        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);

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

        try {
            $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);
            $this->fail('Pengeluaran melebihi stok aktif harus ditolak.');
        } catch (AturanBisnisException $e) {
            $this->assertStringContainsString('diminta 200,00, tersedia 100,00', $e->getMessage());
            $this->assertStringContainsString(
                'tidak dapat digunakan pada hari kerja',
                $e->getMessage(),
            );
        }

        $this->assertSame(StatusDokumen::Draft, $transaksi->fresh()->status);
        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(100.0, (float) $saldo->stok_aktif);
        $this->assertSame(5000.0, (float) $saldo->stok_buffer);
    }

    #[Test]
    public function issue_surat_jalan_generate_nomor_format_benar(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $transaksi = $this->buatDrafSj();

        $this->app->make(PengeluaranService::class)->issue($transaksi, $this->operator->id);

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Issued, $transaksi->status);
        $this->assertMatchesRegularExpression(
            '#^\d+/DTY/PEI/[IVX]+/\d{4}$#',
            (string) $transaksi->nomor_surat_jalan,
        );
    }

    #[Test]
    public function issue_surat_jalan_tanggal_mundur_memakai_periode_tanggal_transaksi(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);

        $transaksi = $this->buatDrafSj([], ['tanggal' => '2026-09-15']);

        $this->app->make(PengeluaranService::class)->issue($transaksi, $this->operator->id);

        $this->assertStringContainsString('/IX/2026', (string) $transaksi->fresh()->nomor_surat_jalan);
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

        $service = $this->app->make(PengeluaranService::class);
        $service->issue($pertama, $this->operator->id);
        $service->issue($kedua, $this->operator->id);

        $pertama->refresh();
        $kedua->refresh();

        $this->assertSame(StatusDokumen::Issued, $pertama->status);
        $this->assertSame(StatusDokumen::Issued, $kedua->status);
        $this->assertNotSame($pertama->nomor_surat_jalan, $kedua->nomor_surat_jalan);
    }

    #[Test]
    public function scrap_tanpa_catatan_ditolak(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyC->id, 500);

        $transaksi = $this->buatDrafScrap([], ['catatan' => null]);

        $this->expectException(AturanBisnisException::class);
        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);
    }

    #[Test]
    public function scrap_dengan_catatan_posting_mengurangi_stok(): void
    {
        $this->isiStok($this->dty, '4D9160', $this->gradeDtyC->id, 500);

        $transaksi = $this->buatDrafScrap();

        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->dty->id)->sole();
        $this->assertSame(400.0, (float) $saldo->stok_aktif);
    }

    #[Test]
    public function void_poy_posted_oleh_admin_mengembalikan_stok(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 400]);
        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);

        $this->app->make(VoidTransaksiService::class)->voidTransaksiKeluar(
            $transaksi->fresh(),
            'Salah input berat timbangan produksi.',
            $this->admin->id,
        );

        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(1000.0, (float) $saldo->stok_aktif, 'Stok harus kembali utuh setelah void.');
    }

    #[Test]
    public function grade_kategori_lain_ditolak_di_detail(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $poy = $this->buatDrafPoy(['grade_id' => $this->gradeDtyA->id]);

        try {
            $this->app->make(PengeluaranService::class)->posting($poy, $this->operator->id);
            $this->fail('Grade DTY pada pengeluaran POY harus ditolak.');
        } catch (AturanBisnisException) {
            $this->assertSame(0, MutasiStok::query()->count());
        }

        $this->isiStok($this->dty, '4D9160', $this->gradeDtyA->id, 1000);
        $sj = $this->buatDrafSj(['grade_id' => $this->gradePoyAl->id]);

        $this->expectException(AturanBisnisException::class);
        $this->app->make(PengeluaranService::class)->issue($sj, $this->operator->id);
    }

    #[Test]
    public function pengeluaran_policy_void_hanya_admin_dan_posting_hanya_role_tulis(): void
    {
        $this->isiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 1000);

        $transaksi = $this->buatDrafPoy(['berat_netto_kg' => 100]);
        $this->app->make(PengeluaranService::class)->posting($transaksi, $this->operator->id);
        $transaksi->refresh();

        $this->assertFalse(Gate::forUser($this->operator)->allows('void', [$transaksi, JenisKeluar::PoyProduksi]));
        $this->assertTrue(Gate::forUser($this->admin)->allows('void', [$transaksi, JenisKeluar::PoyProduksi]));

        $draf = $this->buatDrafPoy(['berat_netto_kg' => 50]);

        $this->assertTrue(Gate::forUser($this->operator)->allows('posting', [$draf, JenisKeluar::PoyProduksi]));
        $this->assertFalse(Gate::forUser($this->manajer)->allows('posting', [$draf, JenisKeluar::PoyProduksi]));
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
     * @param  array<string, mixed>  $gantiDetail
     * @param  array<string, mixed>  $gantiHeader
     */
    private function buatDrafPoy(array $gantiDetail = [], array $gantiHeader = []): TransaksiKeluar
    {
        $transaksi = TransaksiKeluar::create(array_merge([
            'nomor_transaksi' => 'OUT-POY-'.uniqid(),
            'jenis_keluar' => JenisKeluar::PoyProduksi,
            'tanggal' => '2026-10-13',
            'shift' => 1,
            'operator_nama' => 'Budi',
            'catatan' => null,
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ], $gantiHeader));

        $transaksi->detail()->create(array_merge([
            'barang_id' => $this->poy->id,
            'merge' => 'M-POY-1',
            'grade_id' => $this->gradePoyAl->id,
            'berat_netto_kg' => 100,
        ], $gantiDetail));

        return $transaksi;
    }

    /**
     * @param  array<string, mixed>  $gantiDetail
     * @param  array<string, mixed>  $gantiHeader
     */
    private function buatDrafSj(array $gantiDetail = [], array $gantiHeader = []): TransaksiKeluar
    {
        $transaksi = TransaksiKeluar::create(array_merge([
            'nomor_transaksi' => 'OUT-SJ-'.uniqid(),
            'jenis_keluar' => JenisKeluar::DtySuratJalan,
            'nomor_surat_jalan' => null,
            'tanggal' => '2026-10-13',
            'partner_id' => $this->ipci->id,
            'no_kendaraan' => 'B 1234 CD',
            'nama_sopir' => 'Joko',
            'pic_pengirim' => 'R. Jamal',
            'pic_mengetahui' => 'Budi',
            'catatan' => null,
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ], $gantiHeader));

        $transaksi->detail()->create(array_merge([
            'barang_id' => $this->dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => 2,
            'jumlah_bobbin' => 12,
            'berat_netto_kg' => 66,
            'berat_gross_kg' => 71.48,
        ], $gantiDetail));

        return $transaksi;
    }

    /**
     * @param  array<string, mixed>  $gantiDetail
     * @param  array<string, mixed>  $gantiHeader
     */
    private function buatDrafScrap(array $gantiDetail = [], array $gantiHeader = []): TransaksiKeluar
    {
        $transaksi = TransaksiKeluar::create(array_merge([
            'nomor_transaksi' => 'OUT-SCRAP-'.uniqid(),
            'jenis_keluar' => JenisKeluar::DtyScrapReject,
            'tanggal' => '2026-10-13',
            'catatan' => 'Dijual ke pengepul scrap.',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ], $gantiHeader));

        $transaksi->detail()->create(array_merge([
            'barang_id' => $this->dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyC->id,
            'jumlah_bobbin' => 6,
            'berat_netto_kg' => 100,
        ], $gantiDetail));

        return $transaksi;
    }
}
