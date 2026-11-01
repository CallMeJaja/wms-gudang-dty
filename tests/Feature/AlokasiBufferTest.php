<?php

namespace Tests\Feature;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Models\AlokasiBuffer;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\MutasiStok;
use App\Models\TransaksiMasuk;
use App\Models\User;
use App\Services\AlokasiBufferService;
use App\Services\StatusBufferMinggu;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji alokasi buffer Minggu (PRD §5.2.2 / A11–A13).
 *
 * Prasyarat yang disimulasikan sama seperti alur posting penerimaan:
 *   1. stok AKTIF sudah ditambah dari berat kg detail (StokService::tambahStok)
 *   2. AlokasiBufferService memindahkan porsi buffer AKTIF -> BUFFER.
 *
 * DB: SQLite :memory: — MySQL dev tidak tersentuh.
 */
class AlokasiBufferTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private StokService $stok;

    private AlokasiBufferService $alokasi;

    private User $operator;

    private int $nomorUrut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->stok = $this->app->make(StokService::class);
        $this->alokasi = $this->app->make(AlokasiBufferService::class);
        $this->operator = User::factory()->operator()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function pasokan_tujuh_pallet_merge_minggu_menyisihkan_satu_pallet(): void
    {
        $this->kunciHariKerja(); // Selasa

        $barang = $this->barangPoy(true);
        $pasokanKg = $this->pasokanKg();

        $transaksi = $this->terima($barang, 'M-MINGGU-1', $pasokanKg);

        $stok = $this->stok->saldo($barang, 'M-MINGGU-1', $this->gradePoyAl->id);

        $this->assertSame($this->kgPerPallet(), (float) $stok->stok_buffer);
        $this->assertSame($pasokanKg - $this->kgPerPallet(), (float) $stok->stok_aktif);
        // Total stok tidak berubah: hanya pindah pos.
        $this->assertSame($pasokanKg, $stok->jumlah_stok);

        $catatan = AlokasiBuffer::query()->sole();

        $this->assertFalse($catatan->dilewati);
        $this->assertSame($this->kgPerPallet(), (float) $catatan->jumlah_kg);
        $this->assertSame($transaksi->id, $catatan->sumber_transaksi_masuk_id);
        $this->assertSame($barang->id, $catatan->barang_id);
        $this->assertSame('M-MINGGU-1', $catatan->merge);
    }

    #[Test]
    public function pasokan_kurang_dari_tujuh_pallet_melewati_buffer(): void
    {
        $this->kunciHariKerja();

        $barang = $this->barangPoy(true);
        $pasokanKg = $this->pasokanKg() - $this->kgPerPallet(); // 6 pallet

        $this->terima($barang, 'M-MINGGU-1', $pasokanKg);

        $stok = $this->stok->saldo($barang, 'M-MINGGU-1', $this->gradePoyAl->id);

        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame($pasokanKg, (float) $stok->stok_aktif);

        $catatan = AlokasiBuffer::query()->sole();
        $this->assertTrue($catatan->dilewati);
        $this->assertSame(0.0, (float) $catatan->jumlah_kg);

        // Tidak ada mutasi perpindahan pos sama sekali.
        $this->assertSame(
            0,
            MutasiStok::query()
                ->whereIn('jenis_transaksi', ['ALOKASI_BUFFER', 'RILIS_BUFFER'])
                ->count()
        );
    }

    #[Test]
    public function bukan_merge_minggu_tidak_membuat_baris_alokasi_dan_semua_ke_aktif(): void
    {
        $this->kunciHariKerja();

        $barang = $this->barangPoy(false); // is_merge_minggu = false
        $pasokanKg = $this->pasokanKg();

        $this->terima($barang, 'M-BIASA', $pasokanKg);

        $stok = $this->stok->saldo($barang, 'M-BIASA', $this->gradePoyAl->id);

        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame($pasokanKg, (float) $stok->stok_aktif);
        $this->assertSame(0, AlokasiBuffer::query()->count());
    }

    #[Test]
    public function hari_minggu_tidak_ada_alokasi_buffer(): void
    {
        // 2026-10-18 = hari Minggu.
        $minggu = $this->kunciHariKerja('2026-10-18');
        $this->assertSame('7', $minggu->format('N'), 'Tanggal uji harus jatuh pada hari Minggu.');
        $this->assertSame(0, $this->alokasi->sisaHariKerjaMenujuMinggu(now()));

        $barang = $this->barangPoy(true);
        $pasokanKg = $this->pasokanKg();

        $this->terima($barang, 'M-MINGGU-1', $pasokanKg);

        $stok = $this->stok->saldo($barang, 'M-MINGGU-1', $this->gradePoyAl->id);

        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame(0, AlokasiBuffer::query()->count());
    }

    #[Test]
    public function status_draft_tidak_mengubah_stok_maupun_buffer(): void
    {
        $this->kunciHariKerja();

        $barang = $this->barangPoy(true);

        $transaksi = $this->buatTransaksi($barang, 'M-MINGGU-1', $this->pasokanKg(), StatusDokumen::Draft);

        $this->alokasi->prosesAlokasiUntukPenerimaan($transaksi, $transaksi->detail);

        $this->assertSame(0, AlokasiBuffer::query()->count());
        $this->assertNull($this->stok->saldo($barang, 'M-MINGGU-1', $this->gradePoyAl->id));
    }

    #[Test]
    public function buffer_penuh_tidak_menyisihkan_lagi(): void
    {
        $this->kunciHariKerja();

        $barangPenuh = $this->barangPoy(true, 'POY-PENUH');
        $this->isiBufferLangsung($barangPenuh, 'M-PENUH', $this->targetBufferKg());

        $this->assertSame($this->targetBufferKg(), $this->stok->totalBufferMinggu());

        $barangBaru = $this->barangPoy(true, 'POY-BARU');
        $pasokanKg = $this->pasokanKg();

        $transaksi = $this->terima($barangBaru, 'M-BARU', $pasokanKg, prosesAlokasi: false);

        // Alokasi diproses SETELAH stok masuk aktif.
        $this->alokasi->prosesAlokasiUntukPenerimaan($transaksi, $transaksi->detail);

        $stok = $this->stok->saldo($barangBaru, 'M-BARU', $this->gradePoyAl->id);

        $this->assertSame(0.0, (float) $stok->stok_buffer);
        $this->assertSame($pasokanKg, (float) $stok->stok_aktif);
        $this->assertSame($this->targetBufferKg(), $this->stok->totalBufferMinggu());

        // Tetap dicatat sebagai jejak kebijakan (jumlah_kg = 0, bukan dilewati).
        $catatan = AlokasiBuffer::query()->where('barang_id', $barangBaru->id)->sole();
        $this->assertFalse($catatan->dilewati);
        $this->assertSame(0.0, (float) $catatan->jumlah_kg);
    }

    #[Test]
    public function buffer_hampir_penuh_hanya_menyisihkan_sisa_kuota(): void
    {
        $this->kunciHariKerja();

        // Isi buffer sampai target - 1/2 pallet -> sisa kuota 1/2 pallet (< 1 pallet).
        $setengahPallet = $this->kgPerPallet() / 2;
        $terisiKg = $this->targetBufferKg() - $setengahPallet;

        $barangLama = $this->barangPoy(true, 'POY-LAMA');
        $this->isiBufferLangsung($barangLama, 'M-LAMA', $terisiKg);

        $sisaKuota = $this->targetBufferKg() - $this->stok->totalBufferMinggu();
        $this->assertSame($setengahPallet, $sisaKuota);

        $barangBaru = $this->barangPoy(true, 'POY-BARU');
        $pasokanKg = $this->pasokanKg();

        $transaksi = $this->terima($barangBaru, 'M-BARU', $pasokanKg, prosesAlokasi: false);
        $this->alokasi->prosesAlokasiUntukPenerimaan($transaksi, $transaksi->detail);

        $stok = $this->stok->saldo($barangBaru, 'M-BARU', $this->gradePoyAl->id);

        // Hanya sisa kuota yang disisihkan, BUKAN satu pallet penuh.
        $this->assertSame($setengahPallet, (float) $stok->stok_buffer);
        $this->assertSame($pasokanKg - $setengahPallet, (float) $stok->stok_aktif);
        $this->assertSame($this->targetBufferKg(), $this->stok->totalBufferMinggu());

        $catatan = AlokasiBuffer::query()->where('barang_id', $barangBaru->id)->sole();
        $this->assertSame($setengahPallet, (float) $catatan->jumlah_kg);
    }

    #[Test]
    public function dua_merge_minggu_berbeda_tidak_melampaui_target_buffer(): void
    {
        $this->kunciHariKerja();

        $barangA = $this->barangPoy(true, 'POY-A');
        $barangB = $this->barangPoy(true, 'POY-B');
        $pasokanKg = $this->pasokanKg();

        $trxA = $this->terima($barangA, 'M-A', $pasokanKg);
        $trxB = $this->terima($barangB, 'M-B', $pasokanKg);

        $this->assertSame($this->kgPerPallet(), (float) AlokasiBuffer::query()->where('sumber_transaksi_masuk_id', $trxA->id)->value('jumlah_kg'));
        $this->assertSame($this->kgPerPallet(), (float) AlokasiBuffer::query()->where('sumber_transaksi_masuk_id', $trxB->id)->value('jumlah_kg'));

        $this->assertSame($this->kgPerPallet() * 2, $this->stok->totalBufferMinggu());

        // Sisipkan buffer barang A secara langsung agar mendekati target.
        $this->stok->tambahStok($barangA, 'M-A', $this->gradePoyAl->id, $this->targetBufferKg() * 2);
        $this->stok->alokasiKeBuffer(
            $barangA,
            'M-A',
            $this->gradePoyAl->id,
            $this->targetBufferKg() - $this->stok->totalBufferMinggu(),
            'MANUAL'
        );

        $this->assertSame($this->targetBufferKg(), $this->stok->totalBufferMinggu());

        // Penerimaan ketiga (merge lain lagi) tidak boleh menambah buffer.
        $trxC = $this->terima($barangB, 'M-C', $pasokanKg);
        $this->assertSame($this->targetBufferKg(), $this->stok->totalBufferMinggu());

        $catatanC = AlokasiBuffer::query()->where('sumber_transaksi_masuk_id', $trxC->id)->sole();
        $this->assertSame(0.0, (float) $catatanC->jumlah_kg);
    }

    #[Test]
    public function satu_transaksi_dua_grade_tidak_dobel_alokasi(): void
    {
        $this->kunciHariKerja();

        $barang = $this->barangPoy(true);
        $gradeKedua = Grade::create([
            'kode' => 'Am',
            'kategori_id' => $this->kategoriPoy->id,
            'keterangan' => 'POY grade Am',
            'is_active' => true,
        ]);

        $pasokanKg = $this->pasokanKg();

        $transaksi = $this->buatTransaksi($barang, 'M-MULTI', $pasokanKg, StatusDokumen::Posted, [
            ['grade_id' => $gradeKedua->id, 'berat_kg' => $pasokanKg / 2],
        ]);

        // Kedua grade menerima stok AKTIF masing-masing (bukan dalam 1 baris).
        foreach ($transaksi->detail as $detail) {
            $this->stok->tambahStok(
                $detail->barang,
                $detail->merge,
                (int) $detail->grade_id,
                (float) $detail->berat_kg,
                refNomor: (string) $transaksi->nomor_transaksi,
                userId: $this->operator->id,
                tanggal: $transaksi->tanggal,
            );
        }

        $this->alokasi->prosesAlokasiUntukPenerimaan($transaksi, $transaksi->detail);

        // Hanya satu baris alokasi per (barang, merge, tanggal) — UNIQUE DB.
        $this->assertSame(1, AlokasiBuffer::query()->count());
        $this->assertSame($this->kgPerPallet(), (float) AlokasiBuffer::query()->sole()->jumlah_kg);
        $this->assertSame($this->kgPerPallet(), $this->stok->totalBufferMinggu());

        // Porsi buffer diambil dari grade pertama (urut id detail).
        $this->assertSame(
            $this->kgPerPallet(),
            (float) $this->stok->saldo($barang, 'M-MULTI', $this->gradePoyAl->id)->stok_buffer
        );
        $this->assertSame(
            0.0,
            (float) $this->stok->saldo($barang, 'M-MULTI', $gradeKedua->id)->stok_buffer
        );
    }

    #[Test]
    public function status_buffer_mengikuti_tabel_prd(): void
    {
        // Selasa: hari kerja tersisa Selasa..Sabtu = 5, buffer kosong -> Belum Siap.
        $this->kunciHariKerja('2026-10-13');
        $status = $this->alokasi->statusBuffer();
        $this->assertSame(StatusBufferMinggu::BELUM_SIAP, $status->level);
        $this->assertSame(5, $status->sisaHariKerja);
        $this->assertSame($this->targetBufferKg(), $status->sisaKuotaKg);

        // Senin: hari kerja tersisa Senin..Sabtu = 6 (siklus penuh).
        $this->kunciHariKerja('2026-10-12');
        $this->assertSame(6, $this->alokasi->statusBuffer()->sisaHariKerja);

        // Sabtu: 1 hari kerja tersisa, buffer kurang -> Terlambat.
        $this->kunciHariKerja('2026-10-17');
        $this->assertSame(StatusBufferMinggu::TERLAMBAT, $this->alokasi->statusBuffer()->level);

        // Buffer penuh -> Siap (walau Sabtu).
        $barang = $this->barangPoy(true);
        $this->isiBufferLangsung($barang, 'M-PENUH', $this->targetBufferKg());

        $status = $this->alokasi->statusBuffer();
        $this->assertSame(StatusBufferMinggu::SIAP, $status->level);
        $this->assertSame(0.0, $status->sisaKuotaKg);
        $this->assertSame((float) config('wms.ambang_poy.target_buffer_pallet'), $status->bufferPallet);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper skenario
    |--------------------------------------------------------------------------
    */

    /**
     * Pasokan default = ambang pallet hari (>= 7 pallet) dalam kg.
     */
    private function pasokanKg(): float
    {
        return (int) config('wms.ambang_poy.lewati_buffer_jika_kurang_dari') * $this->kgPerPallet();
    }

    /**
     * Terima POY: tambah stok AKTIF lalu jalankan alokasi buffer.
     */
    private function terima(
        Barang $barang,
        string $merge,
        float $beratKg,
        bool $prosesAlokasi = true,
        array $detailTambahan = [],
    ): TransaksiMasuk {
        $transaksi = $this->buatTransaksi($barang, $merge, $beratKg, StatusDokumen::Posted, $detailTambahan);

        foreach ($transaksi->detail as $detail) {
            $this->stok->tambahStok(
                $detail->barang,
                $detail->merge,
                (int) $detail->grade_id,
                (float) $detail->berat_kg,
                refNomor: (string) $transaksi->nomor_transaksi,
                userId: $this->operator->id,
                tanggal: $transaksi->tanggal,
            );
        }

        if ($prosesAlokasi) {
            $this->alokasi->prosesAlokasiUntukPenerimaan($transaksi, $transaksi->detail);
        }

        return $transaksi;
    }

    /**
     * Bangun transaksi masuk POY beserta detailnya (tanpa menyentuh stok).
     *
     * @param  array<int, array{grade_id: int, berat_kg: float}>  $detailTambahan
     */
    private function buatTransaksi(
        Barang $barang,
        string $merge,
        float $beratKg,
        StatusDokumen $status,
        array $detailTambahan = [],
    ): TransaksiMasuk {
        $nomor = 'POY-IPCI-TEST-'.(++$this->nomorUrut);

        $transaksi = TransaksiMasuk::create([
            'nomor_transaksi' => $nomor,
            'jenis_masuk' => JenisMasuk::PoyIpci,
            'tanggal' => now()->toDateString(),
            'merge_poy_asal' => $merge,
            'shift' => 1,
            'status' => $status,
            'created_by' => $this->operator->id,
        ]);

        $baris = array_merge(
            [[
                'grade_id' => $this->gradePoyAl->id,
                'berat_kg' => $beratKg - array_sum(array_column($detailTambahan, 'berat_kg')),
            ]],
            $detailTambahan
        );

        foreach ($baris as $item) {
            $transaksi->detail()->create([
                'barang_id' => $barang->id,
                'merge' => $merge,
                'grade_id' => $item['grade_id'],
                'jumlah_kemasan' => null,
                'satuan_kemasan_id' => null,
                'jumlah_bobbin' => null,
                'berat_kg' => $item['berat_kg'],
            ]);
        }

        return $transaksi->load('detail.barang');
    }

    /**
     * Isi buffer secara langsung (bypass alokasi) untuk menyiapkan skenario kuota.
     */
    private function isiBufferLangsung(Barang $barang, string $merge, float $kgBuffer): void
    {
        $this->stok->tambahStok($barang, $merge, $this->gradePoyAl->id, $kgBuffer * 2, tanggal: now());
        $this->stok->alokasiKeBuffer($barang, $merge, $this->gradePoyAl->id, $kgBuffer, 'SETUP');
    }
}
