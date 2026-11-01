<?php

namespace Tests\Feature;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Exceptions\StokTidakCukupException;
use App\Models\AlokasiBuffer;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\PenerimaanService;
use App\Services\StokService;
use App\Services\VoidTransaksiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji void transaksi masuk: POY, Gate Pass, DTY, dan cerminan keluar.
 *
 * UI legacy sudah dihapus; void diuji lewat VoidTransaksiService dan
 * otorisasi diperiksa lewat Gate policy.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class VoidTransaksiTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private Barang $poy;

    private Barang $dty;

    private Barang $pendukung;

    private SupplierCustomer $ipci;

    private SupplierCustomer $storeIpci;

    private VoidTransaksiService $voidService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja(); // Selasa — hari kerja

        $this->admin = User::factory()->admin()->create();
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

        $this->ipci = SupplierCustomer::create(['kode' => 'IPCI', 'nama' => 'IPCI', 'jenis' => 'BOTH']);
        $this->storeIpci = SupplierCustomer::create(['kode' => 'STORE-IPCI', 'nama' => 'Store IPCI', 'jenis' => 'SUPPLIER']);

        $this->voidService = $this->app->make(VoidTransaksiService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Admin void POY POSTED -> stok aktif berkurang total qty, status VOID
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function admin_void_poy_posted_stok_kembali_dan_status_void(): void
    {
        $berat = 1000.0;
        $transaksi = $this->postingPoy($berat);

        $this->voidService->voidTransaksiMasuk($transaksi, 'Salah input berat timbangan.', $this->admin->id);

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Void, $transaksi->status);
        $this->assertSame('Salah input berat timbangan.', $transaksi->alasan_void);
        $this->assertNotNull($transaksi->voided_at);
        $this->assertSame($this->admin->id, (int) $transaksi->voided_by);

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(0.0, (float) $saldo->stok_aktif);
        $this->assertSame(0.0, (float) $saldo->stok_buffer);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Mutasi VOID tercatat dengan keluar_qty benar
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function mutasi_void_tercatat_dengan_keluar_qty_benar(): void
    {
        $berat = 1000.0;
        $transaksi = $this->postingPoy($berat);

        $this->voidService->voidTransaksiMasuk($transaksi, 'Alasan pembatalan yang cukup panjang.', $this->admin->id);

        $mutasi = MutasiStok::query()
            ->where('jenis_transaksi', JenisMutasi::Void)
            ->where('pos', PosStok::Aktif)
            ->sole();

        $this->assertSame($berat, (float) $mutasi->keluar_qty);
        $this->assertSame(0.0, (float) $mutasi->masuk_qty);
        $this->assertSame(0.0, (float) $mutasi->saldo_akhir_qty);
        $this->assertSame($transaksi->nomor_transaksi, $mutasi->ref_nomor);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'VOID',
            'modul' => 'TRANSAKSI_MASUK',
            'ref_id' => $transaksi->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Void kedua kali ditolak
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_kedua_kali_ditolak(): void
    {
        $transaksi = $this->postingPoy(500.0);

        $this->voidService->voidTransaksiMasuk($transaksi, 'Alasan pertama yang valid.', $this->admin->id);

        $this->expectException(AturanBisnisException::class);
        $this->voidService->voidTransaksiMasuk($transaksi->fresh(), 'Alasan kedua yang valid.', $this->admin->id);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Operator tidak diizinkan void (policy), status tetap POSTED
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function operator_tidak_diizinkan_void_transaksi_masuk(): void
    {
        $transaksi = $this->postingPoy(500.0);

        $this->assertFalse(
            Gate::forUser($this->operator)->allows('void', [$transaksi, JenisMasuk::PoyIpci])
        );
        $this->assertTrue(
            Gate::forUser($this->admin)->allows('void', [$transaksi, JenisMasuk::PoyIpci])
        );

        $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Alasan terlalu pendek ditolak lapis service
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function alasan_pendek_ditolak(): void
    {
        $transaksi = $this->postingPoy(500.0);

        $this->expectException(AturanBisnisException::class);
        $this->voidService->voidTransaksiMasuk($transaksi->fresh(), 'XYZ', $this->admin->id);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Void DRAFT ditolak
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_draft_ditolak(): void
    {
        $transaksi = $this->buatDrafPoy(500.0);

        $this->expectException(AturanBisnisException::class);
        $this->voidService->voidTransaksiMasuk($transaksi, 'Alasan void yang valid.', $this->admin->id);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Buffer sudah dirilis -> void kurangi AKTIF total, tanpa minus
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_setelah_buffer_dirilis_tidak_minus(): void
    {
        $ambangPallet = (int) config('wms.ambang_poy.lewati_buffer_jika_kurang_dari');
        $total = $ambangPallet * $this->kgPerPallet();
        $transaksi = $this->postingPoy($total);

        $stok = $this->app->make(StokService::class);
        $buffer = (float) StokBarang::query()->where('barang_id', $this->poy->id)->value('stok_buffer');
        $this->assertGreaterThan(0, $buffer);

        $stok->rilisBuffer($this->poy, 'M-POY-1', $this->gradePoyAl->id, $buffer, 'RILIS-UJI');

        $saldo = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(0.0, (float) $saldo->stok_buffer);
        $this->assertSame($total, (float) $saldo->stok_aktif);

        $this->voidService->voidTransaksiMasuk($transaksi, 'Void setelah buffer dirilis penuh.', $this->admin->id);

        $akhir = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(0.0, (float) $akhir->stok_aktif);
        $this->assertSame(0.0, (float) $akhir->stok_buffer);
        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);

        // Baris alokasi tidak menggantung: diberi penanda void.
        $alokasi = AlokasiBuffer::query()->firstOrFail();
        $this->assertStringContainsString('Dibatalkan via void', (string) $alokasi->keterangan);
    }

    /*
    |--------------------------------------------------------------------------
    | 7b. Buffer BELUM dirilis -> void tetap berhasil penuh, tanpa minus
    |--------------------------------------------------------------------------
    |
    | Skenario normal: terima POY -> posting (buffer terisi otomatis) ->
    | sadar salah input -> void SELASA (sebelum rilis Minggu). Void harus
    | mengembalikan buffer dulu ke AKTIF, lalu mengurangi total — hasilnya
    | nol di kedua pos, BUKAN ditolak.
    */

    #[Test]
    public function void_sebelum_buffer_dirilis_tetap_berhasil_penuh(): void
    {
        $ambangPallet = (int) config('wms.ambang_poy.lewati_buffer_jika_kurang_dari');
        $total = $ambangPallet * $this->kgPerPallet();
        $transaksi = $this->postingPoy($total);

        $sebelum = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertGreaterThan(0, (float) $sebelum->stok_buffer);
        $this->assertGreaterThan(0, (float) $sebelum->stok_aktif);

        $this->voidService->voidTransaksiMasuk(
            $transaksi,
            'Void sebelum buffer dirilis — koreksi salah input.',
            $this->admin->id
        );

        $akhir = StokBarang::query()->where('barang_id', $this->poy->id)->sole();
        $this->assertSame(0.0, (float) $akhir->stok_aktif);
        $this->assertSame(0.0, (float) $akhir->stok_buffer);
        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);

        // Jejak mutasi: ada RILIS_BUFFER internal + VOID, bukan saldo gantung.
        $this->assertDatabaseHas('mutasi_stok', [
            'ref_nomor' => $transaksi->nomor_transaksi,
            'jenis_transaksi' => JenisMutasi::RilisBuffer->value,
            'pos' => PosStok::Buffer->value,
        ]);
        $this->assertDatabaseHas('mutasi_stok', [
            'ref_nomor' => $transaksi->nomor_transaksi,
            'jenis_transaksi' => JenisMutasi::Void->value,
            'pos' => PosStok::Aktif->value,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Stok tidak cukup -> void ditolak, status tetap POSTED
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_ditolak_bila_stok_tidak_cukup(): void
    {
        $transaksi = $this->postingPoy(1000.0);

        // Sebagian stok "dipakai": kurangi langsung 600 kg.
        $this->app->make(StokService::class)->kurangiStok(
            $this->poy, 'M-POY-1', $this->gradePoyAl->id, 600.0,
            PosStok::Aktif, JenisMutasi::Keluar, 'PAKAI-PRODUKSI',
        );

        try {
            $this->voidService->voidTransaksiMasuk($transaksi->fresh(), 'Alasan void stok kurang valid.', $this->admin->id);
            $this->fail('Void harus ditolak saat stok aktif tidak cukup.');
        } catch (StokTidakCukupException $e) {
            $this->assertSame(StatusDokumen::Posted, $transaksi->fresh()->status);
            $this->assertSame(400.0, (float) StokBarang::query()->where('barang_id', $this->poy->id)->value('stok_aktif'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 9. Void Gate Pass ISSUED -> stok pendukung berkurang
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_gate_pass_issued_stok_pendukung_berkurang(): void
    {
        $transaksi = $this->drafPendukung(100.0);
        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);
        $nomorGp = $transaksi->fresh()->nomor_gate_pass;

        $this->voidService->voidTransaksiMasuk(
            $transaksi->fresh(), 'Gate Pass ganda, batalkan satu.', $this->admin->id,
        );

        $segar = $transaksi->fresh();
        $this->assertSame(StatusDokumen::Void, $segar->status);
        $this->assertSame($nomorGp, $segar->nomor_gate_pass, 'Nomor Gate Pass tetap tersimpan.');
        $this->assertSame(0.0, (float) StokBarang::query()->where('barang_id', $this->pendukung->id)->value('stok_aktif'));
    }

    /*
    |--------------------------------------------------------------------------
    | 10. Void DTY POSTED -> stok DTY berkurang
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_dty_posted_stok_dty_berkurang(): void
    {
        $transaksi = $this->drafDty();
        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $this->assertSame(66.0, (float) StokBarang::query()->where('barang_id', $this->dty->id)->value('stok_aktif'));

        $this->voidService->voidTransaksiMasuk(
            $transaksi->fresh(), 'DTY salah merge asal produksi.', $this->admin->id,
        );

        $this->assertSame(StatusDokumen::Void, $transaksi->fresh()->status);
        $this->assertSame(0.0, (float) StokBarang::query()->where('barang_id', $this->dty->id)->value('stok_aktif'));
    }

    /*
    |--------------------------------------------------------------------------
    | 11. Void keluar menambah kembali stok aktif
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function void_transaksi_keluar_menambah_kembali_stok(): void
    {
        $this->postingPoy(1000.0);
        $stok = $this->app->make(StokService::class);
        $keluar = TransaksiKeluar::create([
            'nomor_transaksi' => 'OUT-001',
            'jenis_keluar' => JenisKeluar::PoyProduksi,
            'tanggal' => '2026-10-13',
            'status' => StatusDokumen::Posted,
            'created_by' => $this->operator->id,
        ]);
        $keluar->detail()->create([
            'barang_id' => $this->poy->id,
            'merge' => 'M-POY-1',
            'grade_id' => $this->gradePoyAl->id,
            'berat_netto_kg' => 400.0,
        ]);
        $stok->kurangiStok($this->poy, 'M-POY-1', $this->gradePoyAl->id, 400.0, PosStok::Aktif, JenisMutasi::Keluar, 'OUT-001');

        $this->assertSame(600.0, (float) StokBarang::query()->where('barang_id', $this->poy->id)->value('stok_aktif'));

        $this->voidService->voidTransaksiKeluar($keluar, 'Surat jalan batal dikirim.', $this->admin->id);

        $this->assertSame(StatusDokumen::Void, $keluar->fresh()->status);
        $this->assertSame(1000.0, (float) StokBarang::query()->where('barang_id', $this->poy->id)->value('stok_aktif'));
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu
    |--------------------------------------------------------------------------
    */

    private function postingPoy(float $berat): TransaksiMasuk
    {
        $transaksi = $this->buatDrafPoy($berat);
        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        return $transaksi->fresh();
    }

    private function buatDrafPoy(float $berat): TransaksiMasuk
    {
        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => 'IN-POY-'.uniqid(),
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

        $transaksi->detail()->create([
            'barang_id' => $this->poy->id,
            'merge' => 'M-POY-1',
            'grade_id' => $this->gradePoyAl->id,
            'berat_kg' => $berat,
        ]);

        return $transaksi;
    }

    private function drafPendukung(float $qty): TransaksiMasuk
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

    private function drafDty(): TransaksiMasuk
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

        $transaksi->detail()->create([
            'barang_id' => $this->dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => 2,
            'satuan_kemasan_id' => $this->satuanBox->id,
            'jumlah_bobbin' => 12,
            'berat_kg' => 66,
            'keterangan' => TransaksiMasukDetail::penandaGross(71.48),
        ]);

        return $transaksi;
    }
}
