<?php

namespace Tests\Feature;

use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\AlokasiBuffer;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\MutasiStok;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\SupplierCustomer;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji service transaksi masuk F2 (posting/issue, buffer, penomoran, gross DTY).
 *
 * UI legacy sudah dihapus; cakupan bisnis diuji lewat PenerimaanService
 * langsung. Draf dibangun lewat model seperti alur backend.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class InboundTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $operator;

    private Barang $poy;

    private Barang $dty;

    private Barang $pendukung;

    private SupplierCustomer $ipci;

    private SupplierCustomer $storeIpci;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja(); // Selasa — hari kerja, buffer Minggu aktif

        $this->operator = User::factory()->operator()->create();

        $this->poy = $this->barangPoy(true);
        $this->dty = $this->barangDty();

        $satuanPcs = Satuan::create(['kode' => Satuan::PCS, 'nama' => 'Pieces']);

        $this->pendukung = Barang::create([
            'kategori_id' => $this->kategoriPendukung->id,
            'kode_barang' => 'PPR TUB DTY',
            'nama_barang' => 'Paper Tube DTY Bullnose',
            'satuan_dasar_id' => $satuanPcs->id,
            'is_merge_minggu' => false,
            'is_active' => true,
        ]);

        Grade::create([
            'kode' => 'C',
            'kategori_id' => $this->kategoriDty->id,
            'keterangan' => 'DTY scrap',
            'is_active' => true,
        ]);

        $this->ipci = SupplierCustomer::create(['kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'BOTH']);
        $this->storeIpci = SupplierCustomer::create(['kode' => 'STORE-IPCI', 'nama' => 'Store IPCI', 'jenis' => 'SUPPLIER']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function draf_belum_mengubah_stok(): void
    {
        $this->buatDrafPoy(1000);

        $this->assertSame(0, StokBarang::query()->count(), 'DRAFT tidak boleh membuat saldo stok.');
        $this->assertSame(0, MutasiStok::query()->count(), 'DRAFT tidak boleh menulis mutasi.');
        $this->assertSame(0, AlokasiBuffer::query()->count());
    }

    #[Test]
    public function posting_poy_menambah_stok_aktif_dan_mutasi(): void
    {
        $berat = 1000.0;

        $transaksi = $this->buatDrafPoy($berat, [
            'jumlah_kemasan' => 2,
            'jumlah_bobbin' => 63,
        ]);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Posted, $transaksi->status);

        $saldo = StokBarang::query()
            ->where('barang_id', $this->poy->id)
            ->where('merge', 'M-POY-1')
            ->where('grade_id', $this->gradePoyAl->id)
            ->sole();

        // Pasokan 1.000 kg < ambang 7 pallet -> buffer dilewati, seluruhnya AKTIF.
        $this->assertSame($berat, (float) $saldo->stok_aktif);
        $this->assertSame(0.0, (float) $saldo->stok_buffer);
        // Kemasan informatif ikut tercatat (A1: bukan akuntansi).
        $this->assertSame(63, (int) $saldo->jumlah_bobbin);
        $this->assertSame(2, (int) $saldo->jumlah_box);

        $mutasi = MutasiStok::query()->where('pos', PosStok::Aktif)->sole();
        $this->assertSame(JenisMutasi::Masuk, $mutasi->jenis_transaksi);
        $this->assertSame($berat, (float) $mutasi->masuk_qty);
        $this->assertSame($berat, (float) $mutasi->saldo_akhir_qty);
        $this->assertSame($transaksi->nomor_transaksi, $mutasi->ref_nomor);
    }

    #[Test]
    public function posting_poy_merge_minggu_mengisi_buffer_satu_pallet(): void
    {
        // Pasokan = ambang pallet x kg per pallet (7 x 576) tanpa hardcode.
        $ambangPallet = (int) config('wms.ambang_poy.lewati_buffer_jika_kurang_dari');
        $pasokan = $ambangPallet * $this->kgPerPallet();

        $transaksi = $this->buatDrafPoy($pasokan);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $saldo = StokBarang::query()
            ->where('barang_id', $this->poy->id)
            ->where('merge', 'M-POY-1')
            ->sole();

        $this->assertSame($this->kgPerPallet(), (float) $saldo->stok_buffer);
        $this->assertSame($pasokan - $this->kgPerPallet(), (float) $saldo->stok_aktif);

        $alokasi = AlokasiBuffer::query()->sole();
        $this->assertFalse($alokasi->dilewati);
        $this->assertSame($this->kgPerPallet(), (float) $alokasi->jumlah_kg);
        $this->assertSame($transaksi->id, (int) $alokasi->sumber_transaksi_masuk_id);

        // Dua baris mutasi ALOKASI_BUFFER (AKTIF keluar, BUFFER masuk).
        $this->assertSame(
            2,
            MutasiStok::query()->where('jenis_transaksi', JenisMutasi::AlokasiBuffer->value)->count()
        );
    }

    #[Test]
    public function poy_tanpa_berat_kg_ditolak_dengan_pesan_konversi(): void
    {
        $pesan = AturanBisnisException::PESAN_POY_WAJIB_KG;

        $transaksi = $this->buatDrafPoy(500);
        $transaksi->detail()->update(['berat_kg' => 0]);

        try {
            $this->app->make(PenerimaanService::class)->posting($transaksi->fresh(), $this->operator->id);
            $this->fail('POY tanpa berat kg harus ditolak.');
        } catch (AturanBisnisException $e) {
            $this->assertSame($pesan, $e->getMessage(), 'Pesan POY wajib kg harus persis sesuai PRD A1b.');
        }

        // Status tetap DRAFT & stok tidak bertambah (rollback).
        $this->assertSame(StatusDokumen::Draft, $transaksi->fresh()->status);
        $this->assertSame(0, StokBarang::query()->count());
    }

    #[Test]
    public function issue_gate_pass_menambah_stok_dan_status_issued(): void
    {
        $transaksi = $this->buatDrafPendukung(3456);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Issued, $transaksi->status);

        $saldo = StokBarang::query()->where('barang_id', $this->pendukung->id)->sole();

        // Satuan kemasan PCS == satuan dasar PCS -> qty 1:1.
        $this->assertSame(3456.0, (float) $saldo->stok_aktif);
        $this->assertSame(0.0, (float) $saldo->stok_buffer, 'Item pendukung tidak memakai buffer.');
    }

    #[Test]
    public function nomor_gate_pass_service_mengikuti_format_dan_naik(): void
    {
        $nomor = $this->app->make(NomorDokumenService::class);

        $pertama = $nomor->generate('GATE_PASS');
        $kedua = $nomor->generate('GATE_PASS');

        $this->assertMatchesRegularExpression('#^\d+/GP/PEI/[IVX]+/\d{4}$#', $pertama);
        $this->assertStringEndsWith('/'.now()->format('Y'), $pertama);
        $this->assertNotSame($pertama, $kedua, 'Counter nomor harus naik untuk dokumen berikutnya.');
    }

    #[Test]
    public function posting_dty_menambah_stok_dty(): void
    {
        $transaksi = $this->buatDrafDty();

        $this->assertSame('AREA PRODUKSI', $transaksi->sumber_internal);
        $this->assertSame('4P1065', $transaksi->merge_poy_asal);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Posted, $transaksi->status);

        $saldo = StokBarang::query()->where('barang_id', $this->dty->id)->sole();
        $this->assertSame(66.0, (float) $saldo->stok_aktif);
        $this->assertSame(2, (int) $saldo->jumlah_box);
        $this->assertSame(12, (int) $saldo->jumlah_bobbin);
    }

    #[Test]
    public function dty_gross_di_luar_toleransi_hanya_peringatan(): void
    {
        $rentang = PenerimaanService::rentangGrossGrade('A');
        $this->assertNotNull($rentang);

        $diLuar = $rentang['max'] + 10;
        $diDalam = ($rentang['min'] + $rentang['max']) / 2;

        $this->assertTrue(PenerimaanService::grossDiLuarToleransi($diLuar, 'A'));
        $this->assertFalse(PenerimaanService::grossDiLuarToleransi($diDalam, 'A'));
        // Grade tanpa entri config (C) tidak pernah dianggap di luar toleransi.
        $this->assertNull(PenerimaanService::rentangGrossGrade('C'));
        $this->assertFalse(PenerimaanService::grossDiLuarToleransi($diLuar, 'C'));

        // Peringatan tidak boleh memblokir posting.
        $transaksi = $this->buatDrafDty([
            'keterangan' => TransaksiMasukDetail::penandaGross($diLuar * 2),
        ]);

        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);
    }

    #[Test]
    public function grade_kategori_lain_ditolak(): void
    {
        $dty = $this->buatDrafDty(['grade_id' => $this->gradePoyAl->id]);

        try {
            $this->app->make(PenerimaanService::class)->posting($dty, $this->operator->id);
            $this->fail('Grade POY pada transaksi DTY harus ditolak.');
        } catch (AturanBisnisException) {
            $this->assertSame(0, StokBarang::query()->count());
        }

        $poy = $this->buatDrafPoy(500, ['grade_id' => $this->gradeDtyA->id]);

        $this->expectException(AturanBisnisException::class);
        $this->app->make(PenerimaanService::class)->posting($poy, $this->operator->id);
    }

    #[Test]
    public function posting_dua_kali_tidak_menggandakan_stok(): void
    {
        $transaksi = $this->buatDrafPoy(500);

        $service = $this->app->make(PenerimaanService::class);
        $service->posting($transaksi, $this->operator->id);

        $this->expectException(AturanBisnisException::class);

        try {
            $service->posting($transaksi->fresh(), $this->operator->id);
        } finally {
            $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
            $this->assertSame(500.0, (float) $saldo->stok_aktif, 'Stok tidak boleh bertambah dua kali.');
            $this->assertSame(1, MutasiStok::query()->where('jenis_transaksi', JenisMutasi::Masuk->value)->count());
        }
    }

    #[Test]
    public function barang_kategori_lain_ditolak_pada_jenis_yang_salah(): void
    {
        $transaksi = $this->buatDrafPoy(500, [
            'barang_id' => $this->dty->id,
            'grade_id' => $this->gradeDtyA->id,
        ]);

        $this->expectException(AturanBisnisException::class);
        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu: bangun draf langsung lewat model
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<string, mixed>  $gantiDetail
     */
    private function buatDrafPoy(float $berat, array $gantiDetail = []): TransaksiMasuk
    {
        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-TEST-'.uniqid(),
            'jenis_masuk' => JenisMasuk::PoyIpci,
            'tanggal' => '2026-10-13',
            'partner_id' => $this->ipci->id,
            'ref_dokumen' => 'SP-001',
            'shift' => 1,
            'operator_nama' => 'Budi',
            'pengirim' => 'R. Jamal',
            'penerima' => 'Budi',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        $transaksi->detail()->create(array_merge([
            'barang_id' => $this->poy->id,
            'merge' => 'M-POY-1',
            'grade_id' => $this->gradePoyAl->id,
            'jumlah_kemasan' => 2,
            'satuan_kemasan_id' => $this->satuanBox->id,
            'jumlah_bobbin' => 63,
            'berat_kg' => $berat,
        ], $gantiDetail));

        return $transaksi;
    }

    private function buatDrafPendukung(float $qty): TransaksiMasuk
    {
        $pcs = Satuan::query()->where('kode', Satuan::PCS)->value('id');

        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-GP-'.uniqid(),
            'jenis_masuk' => JenisMasuk::PendukungGatepass,
            'nomor_gate_pass' => 'GP-'.uniqid(),
            'tanggal' => '2026-10-13',
            'partner_id' => $this->storeIpci->id,
            'ref_dokumen' => 'GP-001',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        $transaksi->detail()->create([
            'barang_id' => $this->pendukung->id,
            'merge' => 'BATCH-A',
            'grade_id' => $this->gradePendukungStd->id,
            'jumlah_kemasan' => $qty,
            'satuan_kemasan_id' => $pcs,
            'berat_kg' => $qty,
            'keterangan' => 'GRN-VELVET',
        ]);

        return $transaksi;
    }

    /**
     * @param  array<string, mixed>  $gantiDetail
     */
    private function buatDrafDty(array $gantiDetail = []): TransaksiMasuk
    {
        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-DTY-'.uniqid(),
            'jenis_masuk' => JenisMasuk::DtyProduksi,
            'tanggal' => '2026-10-13',
            'shift' => 2,
            'operator_nama' => 'Siti',
            'sumber_internal' => 'AREA PRODUKSI',
            'merge_poy_asal' => '4P1065',
            'status' => StatusDokumen::Draft,
            'created_by' => $this->operator->id,
        ]);

        $transaksi->detail()->create(array_merge([
            'barang_id' => $this->dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => 2,
            'satuan_kemasan_id' => $this->satuanBox->id,
            'jumlah_bobbin' => 12,
            'berat_kg' => 66,
            'keterangan' => TransaksiMasukDetail::penandaGross(71.48),
        ], $gantiDetail));

        return $transaksi;
    }
}
