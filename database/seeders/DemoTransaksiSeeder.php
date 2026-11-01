<?php

namespace Database\Seeders;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Models\StokOpname;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\PenerimaanService;
use App\Services\PengeluaranService;
use App\Services\StokOpnameService;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data transaksi demo (dashboard berisi, laporan ada angka, buffer terlihat).
 *
 * Dipanggil dari DatabaseSeeder HANYA di environment local, atau eksplisit:
 * `php artisan db:seed --class=DemoTransaksiSeeder` (jalur eksplisit ini
 * melewati DatabaseSeeder sehingga selalu jalan saat diminta langsung).
 *
 * - Nomor transaksi manual berawalan DEMO- agar jelas data demo.
 * - Idempoten: tiap langkah cek nomor dulu; aman dijalankan ulang.
 * - Tanggal relatif ke hari ini (buffer butuh hari kerja Senin–Sabtu,
 *   sehingga hari Minggu digeser ke Senin berikutnya).
 */
class DemoTransaksiSeeder extends Seeder
{
    public const OPERATOR_NAMA = 'Budi Santoso';

    public const MERGE_POY = '4P1065';

    public const MERGE_DTY = '4D9160';

    public const MERGE_PENDUKUNG = 'GRN-VELVET';

    public function run(): void
    {
        $operator = User::query()->where('username', 'operator')->firstOrFail();
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $tgl = $this->tanggalBasis();
        $userId = (int) $operator->id;

        $poy = Barang::query()->where('kode_barang', 'POY-250-48')->firstOrFail();
        $poy->update(['is_merge_minggu' => true]);

        $this->penerimaanPoy($poy, $tgl, $userId);
        $this->pengeluaranPoy($poy, $tgl, $userId);
        $this->penerimaanDty($tgl, $userId);
        $this->suratJalanDty($tgl, $userId);
        $this->penerimaanPendukung($tgl, $userId);
        $this->drafPoy($userId);
        $this->opnamePendukung((int) $admin->id);
    }

    /**
     * Basis tanggal demo: hari ini, kecuali Minggu (tak ada alokasi buffer)
     * digeser ke Senin berikutnya agar buffer terisi & filter bulan relevan.
     */
    private function tanggalBasis(): Carbon
    {
        $tgl = today();

        return (int) $tgl->format('N') === 7 ? $tgl->addDay() : $tgl;
    }

    private function penerimaanPoy(Barang $poy, Carbon $tgl, int $userId): void
    {
        $grade = $this->grade(KategoriBarang::POY, 'AL');
        $ipci = SupplierCustomer::query()->where('kode', 'IPCI')->firstOrFail();
        $berat = 7 * (float) config('wms.pallet.poy_kg');
        $bobbin = 7 * (int) config('wms.satuan.poy_pallet_bobbin');

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-POY-IN-01'],
            [
                'jenis_masuk' => JenisMasuk::PoyIpci,
                'tanggal' => $tgl,
                'partner_id' => $ipci->id,
                'shift' => 1,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $poy->id,
                'merge' => self::MERGE_POY,
                'grade_id' => $grade->id,
                'jumlah_bobbin' => $bobbin,
                'berat_kg' => $berat,
            ]);
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PenerimaanService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function pengeluaranPoy(Barang $poy, Carbon $tgl, int $userId): void
    {
        $grade = $this->grade(KategoriBarang::POY, 'AL');

        $trx = TransaksiKeluar::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-POY-OUT-01'],
            [
                'jenis_keluar' => JenisKeluar::PoyProduksi,
                'tanggal' => $tgl,
                'shift' => 2,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $poy->id,
                'merge' => self::MERGE_POY,
                'grade_id' => $grade->id,
                'berat_netto_kg' => 3000,
            ]);
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PengeluaranService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function penerimaanDty(Carbon $tgl, int $userId): void
    {
        $dty = Barang::query()->where('kode_barang', 'DTY-150-48-1')->firstOrFail();
        $grade = $this->grade(KategoriBarang::DTY, 'AX');
        $box = Satuan::query()->where('kode', 'BOX')->firstOrFail();
        $jumlahBox = 60;

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-DTY-IN-01'],
            [
                'jenis_masuk' => JenisMasuk::DtyProduksi,
                'tanggal' => $tgl,
                'sumber_internal' => (string) config('wms.inbound.sumber_internal_dty'),
                'shift' => 2,
                'operator_nama' => self::OPERATOR_NAMA,
                'merge_poy_asal' => self::MERGE_POY,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $dty->id,
                'merge' => self::MERGE_DTY,
                'grade_id' => $grade->id,
                'jumlah_kemasan' => $jumlahBox,
                'satuan_kemasan_id' => $box->id,
                'jumlah_bobbin' => (int) ($jumlahBox * (float) config('wms.satuan.dty_box_bobbin')),
                'berat_kg' => round($jumlahBox * (float) config('wms.satuan.dty_box_netto_kg'), 2),
                'keterangan' => TransaksiMasukDetail::penandaGross(
                    round($jumlahBox * (float) config('wms.satuan.dty_box_gross_kg'), 2)
                ),
            ]);
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PenerimaanService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function suratJalanDty(Carbon $tgl, int $userId): void
    {
        $dty = Barang::query()->where('kode_barang', 'DTY-150-48-1')->firstOrFail();
        $grade = $this->grade(KategoriBarang::DTY, 'AX');
        $ipci = SupplierCustomer::query()->where('kode', 'IPCI')->firstOrFail();
        $jumlahBox = 30;

        $trx = TransaksiKeluar::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-DTY-SJ-01'],
            [
                'jenis_keluar' => JenisKeluar::DtySuratJalan,
                'tanggal' => $tgl,
                'partner_id' => $ipci->id,
                'no_kendaraan' => 'B 1234 DEMO',
                'nama_sopir' => 'Joko Demo',
                'pic_pengirim' => self::OPERATOR_NAMA,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $dty->id,
                'merge' => self::MERGE_DTY,
                'grade_id' => $grade->id,
                'jumlah_kemasan' => $jumlahBox,
                'jumlah_bobbin' => (int) ($jumlahBox * (float) config('wms.satuan.dty_box_bobbin')),
                'berat_netto_kg' => round($jumlahBox * (float) config('wms.satuan.dty_box_netto_kg'), 2),
                'berat_gross_kg' => round($jumlahBox * (float) config('wms.satuan.dty_box_gross_kg'), 2),
            ]);
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PengeluaranService::class)->issue($trx->fresh(), $userId);
        }
    }

    private function penerimaanPendukung(Carbon $tgl, int $userId): void
    {
        $grade = $this->grade(KategoriBarang::PENDUKUNG, (string) config('wms.grade_pendukung_default'));
        $pcs = Satuan::query()->where('kode', 'PCS')->firstOrFail();
        $store = SupplierCustomer::query()->where('kode', 'STORE-IPCI')->firstOrFail();
        $tube = Barang::query()->where('kode_barang', 'PPR TUB DTY, BULLNOSE')->firstOrFail();
        $karton = Barang::query()->where('kode_barang', 'CARTON BOX DTY 675x444x278MM, KRAFT 200')->firstOrFail();

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-PENDUKUNG-IN-01'],
            [
                'jenis_masuk' => JenisMasuk::PendukungGatepass,
                'nomor_gate_pass' => 'DEMO-GP-001',
                'tanggal' => $tgl,
                'partner_id' => $store->id,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        foreach ([$tube->id => 3456, $karton->id => 400] as $barangId => $qty) {
            if ($trx->detail()->where('barang_id', $barangId)->count() === 0) {
                $trx->detail()->create([
                    'barang_id' => $barangId,
                    'merge' => self::MERGE_PENDUKUNG,
                    'grade_id' => $grade->id,
                    'jumlah_kemasan' => $qty,
                    'satuan_kemasan_id' => $pcs->id,
                    'berat_kg' => $qty,
                    'keterangan' => self::MERGE_PENDUKUNG,
                ]);
            }
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PenerimaanService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function drafPoy(int $userId): void
    {
        $poy = Barang::query()->where('kode_barang', 'POY-250-48')->firstOrFail();
        $grade = $this->grade(KategoriBarang::POY, 'AL');
        $ipci = SupplierCustomer::query()->where('kode', 'IPCI')->firstOrFail();

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-POY-DRAFT-01'],
            [
                'jenis_masuk' => JenisMasuk::PoyIpci,
                'tanggal' => today(),
                'partner_id' => $ipci->id,
                'shift' => 1,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $poy->id,
                'merge' => '4P1066',
                'grade_id' => $grade->id,
                'berat_kg' => (float) config('wms.pallet.poy_kg'),
            ]);
        }
    }

    private function opnamePendukung(int $adminId): void
    {
        $tube = Barang::query()->where('kode_barang', 'PPR TUB DTY, BULLNOSE')->firstOrFail();
        $grade = $this->grade(KategoriBarang::PENDUKUNG, (string) config('wms.grade_pendukung_default'));
        $tgl = today()->toDateString();

        $sudah = StokOpname::query()
            ->where('barang_id', $tube->id)
            ->where('merge', self::MERGE_PENDUKUNG)
            ->whereDate('tanggal', $tgl)
            ->exists();

        if ($sudah) {
            return;
        }

        $saldo = app(StokService::class)->saldo($tube, self::MERGE_PENDUKUNG, $grade->id);
        $sistem = $saldo === null ? 0.0 : round((float) $saldo->stok_aktif + (float) $saldo->stok_buffer, 2);

        app(StokOpnameService::class)->proses(
            $tube, self::MERGE_PENDUKUNG, $grade->id,
            $sistem + 10, $tgl, $adminId, 'Opname contoh data demo.',
        );
    }

    private function grade(string $kategori, string $kode): Grade
    {
        return Grade::query()
            ->where('kode', $kode)
            ->whereHas('kategori', fn ($q) => $q->where('kode', $kategori))
            ->firstOrFail();
    }
}
