<?php

namespace Tests\Feature;

use App\Filament\Support\PenerimaanDetailMapper;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\TransaksiMasukDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji transformasi state form Filament -> kolom transaksi_masuk_detail.
 *
 * Fokus: box/gross/batch tidak boleh mengubah arti KG sebagai single source
 * of truth; satuan kg tetap menjadi dasar perhitungan.
 */
class PenerimaanDetailMapperTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
    }

    #[Test]
    public function poy_menyimpan_box_sebagai_satuan_kemasan(): void
    {
        $poy = $this->barangPoy();

        $data = PenerimaanDetailMapper::poy([
            'barang_id' => $poy->id,
            'merge' => '  4P1044  ',
            'grade_id' => $this->gradePoyAl->id,
            'jumlah_kemasan' => '7',
            'jumlah_bobbin' => '252',
            'berat_kg' => '4032.00',
            'keterangan' => '',
        ]);

        $this->assertSame($this->satuanBox->id, $data['satuan_kemasan_id']);
        $this->assertSame('4P1044', $data['merge']);
        $this->assertSame(7.0, $data['jumlah_kemasan']);
        $this->assertSame(252, $data['jumlah_bobbin']);
        $this->assertSame(4032.0, $data['berat_kg']);
        $this->assertNull($data['keterangan']);
    }

    #[Test]
    public function dty_menyimpan_gross_sebagai_penanda_di_keterangan(): void
    {
        $dty = $this->barangDty();

        $data = PenerimaanDetailMapper::dty([
            'barang_id' => $dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => '2',
            'jumlah_bobbin' => '12',
            'berat_kg' => '66.00',
            'berat_gross_kg' => '71.48',
            'keterangan' => 'batch A',
        ]);

        $this->assertArrayNotHasKey('berat_gross_kg', $data);
        $this->assertSame($this->satuanBox->id, $data['satuan_kemasan_id']);
        $this->assertSame(66.0, $data['berat_kg']);
        $this->assertStringContainsString('batch A', $data['keterangan']);
        $this->assertStringContainsString('[GROSS=71.48]', $data['keterangan']);
        $this->assertSame(71.48, TransaksiMasukDetail::grossDariKeterangan($data['keterangan']));
    }

    #[Test]
    public function dty_merge_kosong_menjadi_strip(): void
    {
        $dty = $this->barangDty();

        $data = PenerimaanDetailMapper::dty([
            'barang_id' => $dty->id,
            'merge' => '   ',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => '2',
            'jumlah_bobbin' => '12',
            'berat_kg' => '66.00',
            'berat_gross_kg' => '71.48',
        ]);

        $this->assertSame('-', $data['merge']);
    }

    #[Test]
    public function dty_menolak_gross_kurang_dari_netto(): void
    {
        $dty = $this->barangDty();

        $this->expectException(ValidationException::class);

        PenerimaanDetailMapper::dty([
            'barang_id' => $dty->id,
            'merge' => '4D9160',
            'grade_id' => $this->gradeDtyA->id,
            'jumlah_kemasan' => '2',
            'berat_kg' => '66.00',
            'berat_gross_kg' => '60.00',
        ]);
    }

    #[Test]
    public function pendukung_menghitung_berat_dari_qty_dan_satuan(): void
    {
        $pcs = Satuan::create(['kode' => Satuan::PCS, 'nama' => 'Pieces']);

        $barang = Barang::create([
            'kategori_id' => $this->kategoriPendukung->id,
            'kode_barang' => 'PPR TUB DTY',
            'nama_barang' => 'Paper Tube DTY',
            'satuan_dasar_id' => $pcs->id,
            'is_active' => true,
        ]);

        $data = PenerimaanDetailMapper::pendukung([
            'barang_id' => $barang->id,
            'batch' => 'GRN-VELVET',
            'jumlah_kemasan' => '10',
            'satuan_kemasan_id' => $pcs->id,
            'keterangan' => 'Catatan baris',
        ]);

        $this->assertSame(10.0, $data['berat_kg']);
        $this->assertSame($this->gradePendukungStd->id, $data['grade_id']);
        $this->assertSame($pcs->id, $data['satuan_kemasan_id']);
        $this->assertSame('GRN-VELVET', $data['batch']);
        $this->assertSame('Catatan baris', $data['keterangan']);
        $this->assertSame('-', $data['merge']);
    }

    #[Test]
    public function pendukung_menolak_qty_yang_tidak_bisa_dikonversi(): void
    {
        $dty = $this->barangDty();

        $this->expectException(ValidationException::class);

        PenerimaanDetailMapper::pendukung([
            'barang_id' => $dty->id,
            'jumlah_kemasan' => '2',
            'satuan_kemasan_id' => $this->satuanBox->id,
        ]);
    }
}
