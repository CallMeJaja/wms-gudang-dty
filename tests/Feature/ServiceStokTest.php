<?php

namespace Tests\Feature;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Exceptions\AturanBisnisException;
use App\Exceptions\StokTidakCukupException;
use App\Models\KonversiSatuan;
use App\Models\MutasiStok;
use App\Models\NomorDokumen;
use App\Models\StokBarang;
use App\Services\KonversiSatuanService;
use App\Services\NomorDokumenService;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji service layer F2A: ledger stok, perpindahan pos buffer, penomoran
 * dokumen atomik, dan aturan konversi satuan.
 *
 * DB: SQLite :memory: (phpunit.xml) — database MySQL dev tidak tersentuh.
 */
class ServiceStokTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private StokService $stok;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->stok = $this->app->make(StokService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function tambah_stok_menambah_saldo_aktif_dan_menulis_mutasi_dengan_snapshot_benar(): void
    {
        $this->kunciHariKerja();
        $barang = $this->barangDty();

        $this->stok->tambahStok($barang, 'M-001', $this->gradeDtyA->id, 100.0, PosStok::Aktif, JenisMutasi::Masuk, 'IN-1');
        $stok = $this->stok->tambahStok($barang, 'M-001', $this->gradeDtyA->id, 50.5, PosStok::Aktif, JenisMutasi::Masuk, 'IN-2');

        $this->assertSame(150.5, (float) $stok->stok_aktif);
        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame(150.5, $stok->jumlah_stok);

        $mutasi = MutasiStok::query()
            ->where('barang_id', $barang->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $mutasi);

        $this->assertSame(PosStok::Aktif, $mutasi[0]->pos);
        $this->assertSame(JenisMutasi::Masuk, $mutasi[0]->jenis_transaksi);
        $this->assertSame(100.0, (float) $mutasi[0]->masuk_qty);
        $this->assertSame(0.0, (float) $mutasi[0]->keluar_qty);
        $this->assertSame(100.0, (float) $mutasi[0]->saldo_akhir_qty);

        // Snapshot kedua = saldo SETELAH mutasi (100 + 50,5), bukan delta.
        $this->assertSame(150.5, (float) $mutasi[1]->saldo_akhir_qty);

        // Satu baris stok per (barang, merge, grade) — UNIQUE.
        $this->assertSame(1, StokBarang::query()->count());
    }

    #[Test]
    public function kurangi_stok_berhasil_bila_cukup_dan_melempar_exception_bila_melebihi(): void
    {
        $this->kunciHariKerja();
        $barang = $this->barangDty();

        $this->stok->tambahStok($barang, 'M-001', $this->gradeDtyA->id, 200.0);

        $stok = $this->stok->kurangiStok($barang, 'M-001', $this->gradeDtyA->id, 75.0);

        $this->assertSame(125.0, (float) $stok->stok_aktif);

        $mutasiKeluar = MutasiStok::query()
            ->where('jenis_transaksi', JenisMutasi::Keluar->value)
            ->firstOrFail();

        $this->assertSame(75.0, (float) $mutasiKeluar->keluar_qty);
        $this->assertSame(0.0, (float) $mutasiKeluar->masuk_qty);
        $this->assertSame(125.0, (float) $mutasiKeluar->saldo_akhir_qty);

        $this->expectException(StokTidakCukupException::class);

        try {
            $this->stok->kurangiStok($barang, 'M-001', $this->gradeDtyA->id, 500.0);
        } finally {
            // Stok tidak boleh berubah dan tidak ada mutasi tambahan.
            $this->assertSame(125.0, (float) $stok->fresh()->stok_aktif);
            $this->assertSame(2, MutasiStok::query()->count());
        }
    }

    #[Test]
    public function alokasi_ke_buffer_menulis_dua_baris_mutasi_dengan_snapshot_masing_masing_pos(): void
    {
        $this->kunciHariKerja();
        $barang = $this->barangPoy();

        $this->stok->tambahStok($barang, 'M-POY', $this->gradePoyAl->id, 1000.0);

        $this->stok->alokasiKeBuffer($barang, 'M-POY', $this->gradePoyAl->id, 576.0, 'IN-POY-1');

        $stok = $this->stok->saldo($barang, 'M-POY', $this->gradePoyAl->id);

        $this->assertSame(424.0, (float) $stok->stok_aktif);
        $this->assertSame(576.0, (float) $stok->stok_buffer);
        $this->assertSame(1000.0, $stok->jumlah_stok, 'Total stok tidak boleh berubah saat pindah pos.');

        $mutasi = MutasiStok::query()
            ->where('jenis_transaksi', JenisMutasi::AlokasiBuffer->value)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $mutasi, 'ALOKASI_BUFFER wajib 2 baris mutasi.');

        // Baris 1: AKTIF keluar.
        $this->assertSame(PosStok::Aktif, $mutasi[0]->pos);
        $this->assertSame(576.0, (float) $mutasi[0]->keluar_qty);
        $this->assertSame(0.0, (float) $mutasi[0]->masuk_qty);
        $this->assertSame(424.0, (float) $mutasi[0]->saldo_akhir_qty);

        // Baris 2: BUFFER masuk.
        $this->assertSame(PosStok::Buffer, $mutasi[1]->pos);
        $this->assertSame(576.0, (float) $mutasi[1]->masuk_qty);
        $this->assertSame(0.0, (float) $mutasi[1]->keluar_qty);
        $this->assertSame(576.0, (float) $mutasi[1]->saldo_akhir_qty);
    }

    #[Test]
    public function total_buffer_minggu_hanya_menjumlah_barang_ber_tanda_merge_minggu(): void
    {
        $this->kunciHariKerja();

        $poyMinggu = $this->barangPoy(true, 'POY-MINGGU');
        $poyBiasa = $this->barangPoy(false, 'POY-BIASA');

        // Buffer pada barang merge Minggu: 400 kg.
        $this->stok->tambahStok($poyMinggu, 'M-1', $this->gradePoyAl->id, 1000.0);
        $this->stok->alokasiKeBuffer($poyMinggu, 'M-1', $this->gradePoyAl->id, 400.0, 'REF-1');

        // Buffer pada barang non-merge (dipaksa lewat model) tidak boleh dihitung.
        $stokBiasa = StokBarang::create([
            'barang_id' => $poyBiasa->id,
            'merge' => 'M-2',
            'grade_id' => $this->gradePoyAl->id,
            'stok_aktif' => 0,
            'stok_buffer' => 250.0,
        ]);

        $this->assertGreaterThan(0, (float) $stokBiasa->stok_buffer);

        $this->assertSame(400.0, $this->stok->totalBufferMinggu());

        // Merge berbeda pada barang merge Minggu tetap dijumlah lintas merge.
        $this->stok->tambahStok($poyMinggu, 'M-3', $this->gradePoyAl->id, 600.0);
        $this->stok->alokasiKeBuffer($poyMinggu, 'M-3', $this->gradePoyAl->id, 200.0, 'REF-2');

        $this->assertSame(600.0, $this->stok->totalBufferMinggu());
    }

    #[Test]
    public function nomor_dokumen_generate_memakai_format_config_dan_increment_berurutan(): void
    {
        $this->kunciHariKerja('2026-10-13');

        NomorDokumen::firstOrCreate(
            [
                'jenis' => NomorDokumen::SURAT_JALAN_DTY,
                'periode' => NomorDokumen::periodeUntuk(NomorDokumen::SURAT_JALAN_DTY),
            ],
            ['last_number' => 243]
        );

        $service = $this->app->make(NomorDokumenService::class);

        $nomorPertama = $service->generate(NomorDokumen::SURAT_JALAN_DTY);
        $nomorKedua = $service->generate(NomorDokumen::SURAT_JALAN_DTY);

        $template = config('wms.format_nomor.'.NomorDokumen::SURAT_JALAN_DTY);
        $bulanRomawi = config('wms.bulan_romawi.10');

        $this->assertSame("244/DTY/PEI/{$bulanRomawi}/2026", $nomorPertama);
        $this->assertSame("245/DTY/PEI/{$bulanRomawi}/2026", $nomorKedua);
        $this->assertSame('{nomor}/DTY/PEI/{bulan_romawi}/{tahun}', $template);

        // peek() tidak menaikkan counter.
        $this->assertSame("246/DTY/PEI/{$bulanRomawi}/2026", $service->peek(NomorDokumen::SURAT_JALAN_DTY));
        $this->assertSame("246/DTY/PEI/{$bulanRomawi}/2026", $service->peek(NomorDokumen::SURAT_JALAN_DTY));
        $this->assertSame("246/DTY/PEI/{$bulanRomawi}/2026", $service->generate(NomorDokumen::SURAT_JALAN_DTY));

        // Jenis lain memakai template berbeda (TRX_MASUK) — tetap dari config.
        $nomorMasuk = $service->generate(NomorDokumen::TRX_MASUK);
        $this->assertSame('IN-2026-10-1', $nomorMasuk);
    }

    #[Test]
    public function generate_unik_melewati_nomor_yang_sudah_dipakai(): void
    {
        $service = $this->app->make(NomorDokumenService::class);
        $tanggal = Carbon::parse('2026-10-13');
        $dipanggil = 0;

        $nomor = $service->generateUnik(
            NomorDokumen::SURAT_JALAN_DTY,
            function (string $n) use (&$dipanggil): bool {
                $dipanggil++;

                return $dipanggil === 1;
            },
            $tanggal,
        );

        $bulan = config('wms.bulan_romawi.10');

        $this->assertSame(2, $dipanggil);
        $this->assertSame("2/DTY/PEI/{$bulan}/2026", $nomor);
    }

    #[Test]
    public function nomor_dokumen_peek_dan_format_memakai_tanggal_opsional(): void
    {
        $service = $this->app->make(NomorDokumenService::class);
        $tanggal = Carbon::parse('2026-09-15');

        $this->assertSame(
            '1/DTY/PEI/IX/2026',
            $service->peek(NomorDokumen::SURAT_JALAN_DTY, $tanggal)
        );
        $this->assertSame(
            '7/DTY/PEI/IX/2026',
            $service->format(NomorDokumen::SURAT_JALAN_DTY, 7, $tanggal)
        );
    }

    #[Test]
    public function validasi_berat_poy_melempar_pesan_persis_bila_kg_kosong(): void
    {
        $service = $this->app->make(KonversiSatuanService::class);

        $pesan = AturanBisnisException::PESAN_POY_WAJIB_KG;
        $this->assertSame(
            'Berat Netto (Kg) wajib diisi untuk POY — konversi otomatis tidak tersedia.',
            $pesan
        );

        foreach ([[null, 36], [null, null], [0.0, 10]] as [$kg, $bobbin]) {
            try {
                $service->validasiBeratPoy($kg, $bobbin);
                $this->fail('validasiBeratPoy seharusnya melempar AturanBisnisException.');
            } catch (AturanBisnisException $e) {
                $this->assertSame($pesan, $e->getMessage());
            }
        }

        // Tidak melempar bila kg terisi dari timbangan.
        $service->validasiBeratPoy(576.0, null);
        $this->assertTrue(true);
    }

    #[Test]
    public function konversi_kg_null_untuk_poy_dan_benar_untuk_dty(): void
    {
        $poy = $this->barangPoy();
        $dty = $this->barangDty();

        KonversiSatuan::create([
            'barang_id' => $dty->id,
            'dari_satuan_id' => $this->satuanBox->id,
            'ke_satuan_id' => $this->satuanKg->id,
            'faktor' => config('wms.satuan.dty_box_netto_kg'),
            'is_default' => true,
        ]);
        KonversiSatuan::create([
            'barang_id' => $dty->id,
            'dari_satuan_id' => $this->satuanBox->id,
            'ke_satuan_id' => $this->satuanBobbin->id,
            'faktor' => config('wms.satuan.dty_box_bobbin'),
            'is_default' => true,
        ]);
        KonversiSatuan::create([
            'barang_id' => $dty->id,
            'dari_satuan_id' => $this->satuanBobbin->id,
            'ke_satuan_id' => $this->satuanKg->id,
            'faktor' => config('wms.satuan.dty_bobbin_kg'),
            'is_default' => true,
        ]);

        $service = $this->app->make(KonversiSatuanService::class);

        $this->assertSame(330.0, $service->hitungKgDariKemasan($dty, 10, $this->satuanBox->id));
        // Konversi berantai BOX -> BOBBIN -> KG untuk barang tanpa baris BOX->KG.
        $tanpaLangsung = $this->barangDty('DTY-TANPA-BOX-KG');
        foreach (KonversiSatuan::all() as $baris) {
            KonversiSatuan::create([
                'barang_id' => $tanpaLangsung->id,
                'dari_satuan_id' => $baris->dari_satuan_id,
                'ke_satuan_id' => $baris->ke_satuan_id,
                'faktor' => $baris->faktor,
                'is_default' => true,
            ]);
        }
        KonversiSatuan::query()
            ->where('barang_id', $tanpaLangsung->id)
            ->where('dari_satuan_id', $this->satuanBox->id)
            ->where('ke_satuan_id', $this->satuanKg->id)
            ->delete();

        $this->assertSame(330.0, $service->hitungKgDariKemasan($tanpaLangsung, 10, $this->satuanBox->id));

        // POY tidak memiliki konversi otomatis.
        $this->assertNull($service->hitungKgDariKemasan($poy, 10, $this->satuanBobbin->id));

        // Auto-fill DTY dari config.
        $auto = $service->autoFillDty(10);
        $this->assertSame((int) config('wms.satuan.dty_box_bobbin') * 10, $auto['bobbin']);
        $this->assertSame(330.0, $auto['netto_kg']);
        $this->assertEqualsWithDelta(
            (float) config('wms.satuan.dty_box_gross_kg') * 10,
            $auto['gross_kg'],
            0.01
        );
    }

    #[Test]
    public function grade_dari_kategori_lain_ditolak(): void
    {
        $this->kunciHariKerja();
        $barangDty = $this->barangDty();

        $this->expectException(AturanBisnisException::class);

        // gradePoyAl milik kategori POY, barang ber-kategori DTY.
        $this->stok->tambahStok($barangDty, 'M-001', $this->gradePoyAl->id, 10.0);
    }

    #[Test]
    public function dty_tidak_boleh_masuk_pos_buffer(): void
    {
        $this->kunciHariKerja();
        $barangDty = $this->barangDty();

        $this->expectException(AturanBisnisException::class);

        $this->stok->tambahStok($barangDty, 'M-001', $this->gradeDtyA->id, 10.0, PosStok::Buffer);
    }

    #[Test]
    public function rilis_buffer_memindahkan_buffer_ke_aktif_dengan_dua_baris_mutasi(): void
    {
        $this->kunciHariKerja();
        $barang = $this->barangPoy();

        $this->stok->tambahStok($barang, 'M-POY', $this->gradePoyAl->id, 1000.0);
        $this->stok->alokasiKeBuffer($barang, 'M-POY', $this->gradePoyAl->id, 576.0, 'REF-ALOKASI');
        $this->stok->rilisBuffer($barang, 'M-POY', $this->gradePoyAl->id, 576.0, 'REF-RILIS');

        $stok = $this->stok->saldo($barang, 'M-POY', $this->gradePoyAl->id);

        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame(1000.0, (float) $stok->stok_aktif);

        $mutasi = MutasiStok::query()
            ->where('jenis_transaksi', JenisMutasi::RilisBuffer->value)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $mutasi);
        $this->assertSame(PosStok::Buffer, $mutasi[0]->pos);
        $this->assertSame(0.0, (float) $mutasi[0]->saldo_akhir_qty);
        $this->assertSame(PosStok::Aktif, $mutasi[1]->pos);
        $this->assertSame(1000.0, (float) $mutasi[1]->saldo_akhir_qty);
    }
}
