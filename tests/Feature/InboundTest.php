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
use App\Models\User;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji modul transaksi masuk F2B (controller + policy + validasi + UI HTTP).
 *
 * Cakupan: RBAC (Admin/Operator/Manajer/tamu), DRAFT tidak mengubah stok,
 * posting POY mengisi stok & buffer Minggu, issue Gate Pass, auto-fill &
 * peringatan gross DTY, serta penolakan grade lintas kategori.
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class InboundTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    private Barang $poy;

    private Barang $dty;

    private Barang $pendukung;

    private Grade $gradePoyAm;

    private Grade $gradeDtyB;

    private Grade $gradeDtyC;

    private SupplierCustomer $ipci;

    private SupplierCustomer $storeIpci;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja(); // Selasa — hari kerja, buffer Minggu aktif

        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();

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

        $this->gradePoyAm = Grade::create([
            'kode' => 'Am',
            'kategori_id' => $this->kategoriPoy->id,
            'keterangan' => 'POY grade Am',
            'is_active' => true,
        ]);

        $this->gradeDtyB = Grade::create([
            'kode' => 'B',
            'kategori_id' => $this->kategoriDty->id,
            'keterangan' => 'DTY grade B',
            'is_active' => true,
        ]);

        // Grade C sengaja TIDAK ada di config toleransi gross (PRD §3.3).
        $this->gradeDtyC = Grade::create([
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

    /*
    |--------------------------------------------------------------------------
    | 1. RBAC: Operator boleh index + create POY
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function operator_bisa_membuka_index_dan_create_poy(): void
    {
        $this->actingAs($this->operator)
            ->get(route('inbound.poy.index'))
            ->assertOk()
            ->assertSee('Penerimaan POY (IPCI)');

        $this->actingAs($this->operator)
            ->get(route('inbound.poy.create'))
            ->assertOk()
            ->assertSee('Berat Netto (Kg) wajib — konversi otomatis tidak tersedia.');
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Manajer: index 200, create 403
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function manajer_bisa_index_tapi_tidak_bisa_create(): void
    {
        $this->actingAs($this->manajer)
            ->get(route('inbound.poy.index'))
            ->assertOk();

        $this->actingAs($this->manajer)
            ->get(route('inbound.poy.create'))
            ->assertForbidden();

        // Route tulis juga ditolak (tolak.manajer).
        $this->actingAs($this->manajer)
            ->post(route('inbound.poy.store'), $this->payloadPoy())
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Tamu diarahkan ke login
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function tamu_diarahkan_ke_login(): void
    {
        $this->get(route('inbound.poy.index'))->assertRedirect(route('login'));
        $this->get(route('inbound.dty.index'))->assertRedirect(route('login'));
        $this->get(route('inbound.pendukung.index'))->assertRedirect(route('login'));
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Simpan POY DRAFT -> stok TIDAK bertambah
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function simpan_poy_sebagai_draft_tidak_mengubah_stok(): void
    {
        $this->actingAs($this->operator)
            ->post(route('inbound.poy.store'), $this->payloadPoy(['berat_kg' => 1000]))
            ->assertRedirect();

        $transaksi = TransaksiMasuk::query()->sole();

        $this->assertSame(StatusDokumen::Draft, $transaksi->status);
        $this->assertSame(JenisMasuk::PoyIpci, $transaksi->jenis_masuk);
        $this->assertSame(0, StokBarang::query()->count(), 'DRAFT tidak boleh membuat saldo stok.');
        $this->assertSame(0, MutasiStok::query()->count(), 'DRAFT tidak boleh menulis mutasi.');
        $this->assertSame(0, AlokasiBuffer::query()->count());

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'CREATE',
            'modul' => 'TRANSAKSI_MASUK_POY',
            'ref_id' => $transaksi->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Posting POY -> stok aktif bertambah + mutasi tercatat
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function posting_poy_menambah_stok_aktif_dan_mutasi(): void
    {
        $berat = 1000.0;

        $transaksi = $this->buatDrafPoy([
            'berat_kg' => $berat,
            'jumlah_box' => 2,
            'jumlah_bobbin' => 63,
        ]);

        $this->actingAs($this->operator)
            ->post(route('inbound.poy.posting', $transaksi))
            ->assertRedirect(route('inbound.poy.show', $transaksi))
            ->assertSessionHas('sukses');

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

    /*
    |--------------------------------------------------------------------------
    | 6. Posting POY merge Minggu >= ambang pallet -> buffer 1 pallet
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function posting_poy_merge_minggu_mengisi_buffer_satu_pallet(): void
    {
        // Pasokan = ambang pallet x kg per pallet (7 x 576) tanpa hardcode.
        $ambangPallet = (int) config('wms.ambang_poy.lewati_buffer_jika_kurang_dari');
        $pasokan = $ambangPallet * $this->kgPerPallet();

        $transaksi = $this->buatDrafPoy(['berat_kg' => $pasokan]);

        $this->actingAs($this->operator)
            ->post(route('inbound.poy.posting', $transaksi))
            ->assertRedirect();

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

    /*
    |--------------------------------------------------------------------------
    | 7. POY tanpa berat_kg -> pesan persis yang diminta
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function poy_tanpa_berat_kg_ditolak_dengan_pesan_konversi(): void
    {
        // 1) Lewat HTTP: berat kg kosong ditolak validasi (pesan wajib isi).
        $this->actingAs($this->operator)
            ->post(route('inbound.poy.store'), $this->payloadPoy([
                'berat_kg' => '',
                'jumlah_bobbin' => 63,
            ]))
            ->assertSessionHasErrors(['detail.0.berat_kg']);

        $this->assertSame(0, TransaksiMasuk::query()->count());

        // 2) Lapis aturan bisnis (A1b): pesan PERSIS dari AturanBisnisException
        //    dipakai saat validasi akhir posting (mis. data lama/diubah manual).
        $pesan = AturanBisnisException::PESAN_POY_WAJIB_KG;

        $transaksi = $this->buatDrafPoy(['berat_kg' => 500]);
        $transaksi->detail()->update(['berat_kg' => 0]);

        $this->actingAs($this->operator)
            ->from(route('inbound.poy.show', $transaksi))
            ->post(route('inbound.poy.posting', $transaksi))
            ->assertSessionHasErrors('posting');

        $this->assertSame(
            $pesan,
            session('errors')->first('posting'),
            'Pesan POY wajib kg harus persis sesuai PRD A1b.'
        );

        // Status tetap DRAFT & stok tidak bertambah (rollback).
        $this->assertSame(StatusDokumen::Draft, $transaksi->fresh()->status);
        $this->assertSame(0, StokBarang::query()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Pendukung: issue -> stok bertambah + status ISSUED
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function issue_gate_pass_menambah_stok_dan_status_issued(): void
    {
        $transaksi = $this->buatDrafPendukung(3456);

        $this->actingAs($this->operator)
            ->post(route('inbound.pendukung.issue', $transaksi))
            ->assertRedirect(route('inbound.pendukung.show', $transaksi));

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Issued, $transaksi->status);

        $saldo = StokBarang::query()->where('barang_id', $this->pendukung->id)->sole();

        // Satuan kemasan PCS == satuan dasar PCS -> qty 1:1.
        $this->assertSame(3456.0, (float) $saldo->stok_aktif);
        $this->assertSame(0.0, (float) $saldo->stok_buffer, 'Item pendukung tidak memakai buffer.');

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'UPDATE',
            'modul' => 'TRANSAKSI_MASUK_PENDUKUNG',
            'ref_id' => $transaksi->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 9. Pendukung: nomor_gate_pass ter-generate & berformat
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function nomor_gate_pass_digenerate_otomatis_dan_sesuai_format(): void
    {
        $this->actingAs($this->operator)
            ->post(route('inbound.pendukung.store'), $this->payloadPendukung(100, nomorGatePass: ''))
            ->assertRedirect();

        $transaksi = TransaksiMasuk::query()->sole();
        $harapan = $this->app->make(NomorDokumenService::class)->peek('GATE_PASS');

        // peek() setelah generate = nomor berikutnya, jadi format dicek langsung.
        $pola = '#^\d+/GP/PEI/[IVX]+/\d{4}$#';
        $this->assertMatchesRegularExpression($pola, (string) $transaksi->nomor_gate_pass);

        // Format template dari config dipakai apa adanya.
        $template = (string) config('wms.format_nomor.GATE_PASS');
        $this->assertStringContainsString('{nomor}', $template);
        $this->assertStringEndsWith('/'.now()->format('Y'), (string) $transaksi->nomor_gate_pass);
        $this->assertNotSame($harapan, $transaksi->nomor_gate_pass);

        // Counter naik: dokumen berikutnya mendapat nomor berbeda.
        $kedua = $this->buatDrafPendukung(50);
        $this->assertNotSame($transaksi->nomor_gate_pass, $kedua->nomor_gate_pass);
    }

    /*
    |--------------------------------------------------------------------------
    | 10. DTY: posting -> stok DTY bertambah
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function posting_dty_menambah_stok_dty(): void
    {
        $transaksi = $this->buatDrafDty([
            'jumlah_box' => 2,
            'berat_kg' => 66,
            'berat_gross_kg' => 71.48,
        ]);

        $this->assertSame('AREA PRODUKSI', $transaksi->sumber_internal);
        $this->assertSame('4P1065', $transaksi->merge_poy_asal);

        $this->actingAs($this->operator)
            ->post(route('inbound.dty.posting', $transaksi))
            ->assertRedirect(route('inbound.dty.show', $transaksi));

        $transaksi->refresh();
        $this->assertSame(StatusDokumen::Posted, $transaksi->status);

        $saldo = StokBarang::query()->where('barang_id', $this->dty->id)->sole();
        $this->assertSame(66.0, (float) $saldo->stok_aktif);
        $this->assertSame(2, (int) $saldo->jumlah_box);
        $this->assertSame(12, (int) $saldo->jumlah_bobbin);
    }

    /*
    |--------------------------------------------------------------------------
    | 11. DTY: gross di luar toleransi -> PERINGATAN (bukan blokir)
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function dty_gross_di_luar_toleransi_hanya_peringatan(): void
    {
        $rentang = PenerimaanService::rentangGrossGrade('A');
        $this->assertNotNull($rentang);

        // Gross per box jauh di atas batas atas grade A.
        $box = 1;
        $gross = $rentang['max'] + 10;

        $this->actingAs($this->operator)
            ->post(route('inbound.dty.store'), $this->payloadDty([
                'jumlah_box' => $box,
                'berat_kg' => 33,
                'berat_gross_kg' => $gross,
            ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('peringatan');

        $transaksi = TransaksiMasuk::query()->sole();
        $this->assertSame(StatusDokumen::Draft, $transaksi->status, 'Peringatan tidak boleh memblokir simpan.');

        // Halaman detail tetap menampilkan peringatan yang sama.
        $this->actingAs($this->operator)
            ->get(route('inbound.dty.show', $transaksi))
            ->assertOk()
            ->assertSee('di luar toleransi', false);
    }

    /*
    |--------------------------------------------------------------------------
    | 12. Grade milik kategori lain ditolak
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function grade_kategori_lain_ditolak(): void
    {
        // Grade AL (POY) dipakai pada barang DTY -> harus ditolak.
        $this->actingAs($this->operator)
            ->post(route('inbound.dty.store'), $this->payloadDty([
                'grade_id' => $this->gradePoyAl->id,
            ]))
            ->assertSessionHasErrors('detail.0.grade_id');

        $this->assertSame(0, TransaksiMasuk::query()->count());

        $this->actingAs($this->operator)
            ->post(route('inbound.poy.store'), $this->payloadPoy([
                'grade_id' => $this->gradeDtyA->id,
            ]))
            ->assertSessionHasErrors('detail.0.grade_id');
    }

    /*
    |--------------------------------------------------------------------------
    | 13. Anti double-posting (lapis service)
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function posting_dua_kali_tidak_menggandakan_stok(): void
    {
        $transaksi = $this->buatDrafPoy(['berat_kg' => 500]);

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

    /*
    |--------------------------------------------------------------------------
    | 14. Barang lintas jenis (DTY dipakai di transaksi POY) ditolak
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function barang_kategori_lain_ditolak_pada_jenis_yang_salah(): void
    {
        $this->actingAs($this->operator)
            ->post(route('inbound.poy.store'), $this->payloadPoy([
                'barang_id' => $this->dty->id,
                'grade_id' => $this->gradeDtyA->id,
            ]))
            ->assertSessionHasErrors('detail.0.barang_id');
    }

    /*
    |--------------------------------------------------------------------------
    | 15. Halaman detail tampil + delete draf
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function detail_tampil_dan_draf_bisa_dihapus(): void
    {
        $transaksi = $this->buatDrafPoy(['berat_kg' => 120]);

        $this->actingAs($this->operator)
            ->get(route('inbound.poy.show', $transaksi))
            ->assertOk()
            ->assertSee($transaksi->nomor_transaksi)
            ->assertSee('120,00');

        $this->actingAs($this->operator)
            ->delete(route('inbound.poy.destroy', $transaksi))
            ->assertRedirect(route('inbound.poy.index'));

        $this->assertSame(0, TransaksiMasuk::query()->count());
        $this->assertDatabaseHas('activity_logs', ['event' => 'DELETE', 'modul' => 'TRANSAKSI_MASUK_POY']);
    }

    /*
    |--------------------------------------------------------------------------
    | 16. Draf yang sudah POSTED tidak bisa dihapus / diedit
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function transaksi_posted_tidak_bisa_diedit_atau_dihapus(): void
    {
        $transaksi = $this->buatDrafPoy(['berat_kg' => 200]);
        $this->app->make(PenerimaanService::class)->posting($transaksi, $this->operator->id);

        $this->actingAs($this->operator)
            ->get(route('inbound.poy.edit', $transaksi->fresh()))
            ->assertForbidden();

        $this->actingAs($this->operator)
            ->delete(route('inbound.poy.destroy', $transaksi->fresh()))
            ->assertForbidden();

        $this->actingAs($this->operator)
            ->post(route('inbound.poy.posting', $transaksi->fresh()))
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu: payload HTTP
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function payloadPoy(array $ganti = []): array
    {
        return [
            'tanggal' => '2026-10-13',
            'partner_id' => $this->ipci->id,
            'ref_dokumen' => 'SP-001',
            'shift' => 1,
            'operator_nama' => 'Budi',
            'pengirim' => 'R. Jamal',
            'penerima' => 'Budi',
            'detail' => [
                array_merge([
                    'barang_id' => $this->poy->id,
                    'merge' => 'M-POY-1',
                    'grade_id' => $this->gradePoyAl->id,
                    'jumlah_box' => '',
                    'jumlah_bobbin' => '',
                    'berat_kg' => 1000,
                ], $ganti),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function payloadPendukung(float $qty = 100, string $nomorGatePass = '27041797'): array
    {
        $pcs = Satuan::query()->where('kode', Satuan::PCS)->value('id');

        return [
            'nomor_gate_pass' => $nomorGatePass,
            'tanggal' => '2026-10-13',
            'partner_id' => $this->storeIpci->id,
            'ref_dokumen' => 'GP-001',
            'detail' => [
                [
                    'barang_id' => $this->pendukung->id,
                    // Item pendukung tidak memakai grade (Gate Pass tanpa grade);
                    // grade netral kategori PENDUKUNG diisi controller.
                    'merge' => 'BATCH-A',
                    'jumlah_kemasan' => $qty,
                    'satuan_kemasan_id' => $pcs,
                    'keterangan' => 'GRN-VELVET',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function payloadDty(array $ganti = []): array
    {
        return [
            'tanggal' => '2026-10-13',
            'shift' => 2,
            'operator_nama' => 'Siti',
            'sumber_internal' => 'AREA PRODUKSI',
            'merge_poy_asal' => '4P1065',
            'detail' => [
                array_merge([
                    'barang_id' => $this->dty->id,
                    'merge' => '4D9160',
                    'grade_id' => $this->gradeDtyA->id,
                    'jumlah_box' => 2,
                    'jumlah_bobbin' => 12,
                    'berat_kg' => 66,
                    'berat_gross_kg' => 71.48,
                ], $ganti),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Pembantu: buat draf lewat HTTP (menguji jalur penuh)
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<string, mixed>  $ganti
     */
    private function buatDrafPoy(array $ganti = []): TransaksiMasuk
    {
        $this->actingAs($this->operator)
            ->post(route('inbound.poy.store'), $this->payloadPoy($ganti))
            ->assertSessionHasNoErrors();

        return TransaksiMasuk::query()->latest('id')->firstOrFail();
    }

    private function buatDrafPendukung(float $qty = 100): TransaksiMasuk
    {
        $nomor = 'GP-'.uniqid();

        $this->actingAs($this->operator)
            ->post(route('inbound.pendukung.store'), $this->payloadPendukung($qty, $nomor))
            ->assertSessionHasNoErrors();

        return TransaksiMasuk::query()->latest('id')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $ganti
     */
    private function buatDrafDty(array $ganti = []): TransaksiMasuk
    {
        $this->actingAs($this->operator)
            ->post(route('inbound.dty.store'), $this->payloadDty($ganti))
            ->assertSessionHasNoErrors();

        return TransaksiMasuk::query()->latest('id')->firstOrFail();
    }
}
