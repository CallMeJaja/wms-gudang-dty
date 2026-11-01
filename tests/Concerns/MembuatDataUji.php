<?php

namespace Tests\Concerns;

use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use Carbon\Carbon;

/**
 * Fixture bersama untuk test service layer F2A.
 *
 * Selalu menyimpan referensi kategori & grade POY/DTY agar test dapat
 * membangun skenario buffer tanpa menyentuh database dev (phpunit.xml
 * mengarah ke SQLite :memory:).
 */
trait MembuatDataUji
{
    protected KategoriBarang $kategoriPoy;

    protected KategoriBarang $kategoriDty;

    protected KategoriBarang $kategoriPendukung;

    protected Grade $gradePoyAl;

    protected Grade $gradeDtyA;

    protected Grade $gradePendukungStd;

    protected Satuan $satuanKg;

    protected Satuan $satuanBox;

    protected Satuan $satuanBobbin;

    protected function siapkanMasterDasar(): void
    {
        $this->kategoriPoy = KategoriBarang::create(['kode' => KategoriBarang::POY, 'nama' => 'POY']);
        $this->kategoriDty = KategoriBarang::create(['kode' => KategoriBarang::DTY, 'nama' => 'DTY']);
        $this->kategoriPendukung = KategoriBarang::create(['kode' => KategoriBarang::PENDUKUNG, 'nama' => 'Pendukung']);

        $this->gradePoyAl = Grade::create([
            'kode' => 'AL',
            'kategori_id' => $this->kategoriPoy->id,
            'keterangan' => 'POY grade AL',
            'is_active' => true,
        ]);

        $this->gradeDtyA = Grade::create([
            'kode' => 'A',
            'kategori_id' => $this->kategoriDty->id,
            'keterangan' => 'DTY grade A',
            'is_active' => true,
        ]);

        // Grade netral item pendukung (Gate Pass tidak memuat grade),
        // tetapi transaksi_masuk_detail.grade_id NOT NULL. Wajib ada di
        // master karena grade bersifat tertutup dan wajib terdaftar.
        $this->gradePendukungStd = Grade::create([
            'kode' => (string) config('wms.grade_pendukung_default', 'STD'),
            'kategori_id' => $this->kategoriPendukung->id,
            'keterangan' => 'Grade netral item pendukung',
            'is_active' => true,
        ]);

        $this->satuanKg = Satuan::create(['kode' => Satuan::KG, 'nama' => 'Kilogram']);
        $this->satuanBox = Satuan::create(['kode' => 'BOX', 'nama' => 'Box']);
        $this->satuanBobbin = Satuan::create(['kode' => 'BOBBIN', 'nama' => 'Bobbin']);
    }

    /**
     * Barang POY; is_merge_minggu menentukan apakah ikut alokasi buffer.
     */
    protected function barangPoy(bool $mergeMinggu = true, string $kode = 'POY-250-48'): Barang
    {
        return Barang::create([
            'kategori_id' => $this->kategoriPoy->id,
            'kode_barang' => $kode,
            'nama_barang' => 'POY 250/48',
            'denier' => '250',
            'filament' => '48',
            'satuan_dasar_id' => $this->satuanKg->id,
            'is_merge_minggu' => $mergeMinggu,
            'is_active' => true,
        ]);
    }

    protected function barangDty(string $kode = 'DTY-150-48-1'): Barang
    {
        return Barang::create([
            'kategori_id' => $this->kategoriDty->id,
            'kode_barang' => $kode,
            'nama_barang' => 'DTY 150/48',
            'denier' => '150',
            'filament' => '48',
            'satuan_dasar_id' => $this->satuanKg->id,
            'is_merge_minggu' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Nilai pokok konfigurasi (agar test tidak menulis angka bisnis sendiri).
     */
    protected function kgPerPallet(): float
    {
        return (float) config('wms.pallet.poy_kg');
    }

    protected function targetBufferKg(): float
    {
        return (int) config('wms.ambang_poy.target_buffer_pallet') * $this->kgPerPallet();
    }

    /**
     * Kunci waktu ke hari kerja (default Selasa 10:00) pada zona waktu aplikasi.
     *
     * Catatan: Carbon::parse() memakai zona default PHP (UTC), bukan zona
     * aplikasi. Karena itu tanggal dibangun lewat createFromFormat() dengan
     * zona waktu aplikasi eksplisit agar hari yang dinilai benar-benar
     * sesuai tanggal bisnis (WIB), bukan sisa jam UTC.
     */
    protected function kunciHariKerja(string $tanggal = '2026-10-13'): Carbon
    {
        $zona = config('app.timezone');

        $saat = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal.' 10:00:00', $zona);

        Carbon::setTestNow($saat);

        return $saat;
    }
}
