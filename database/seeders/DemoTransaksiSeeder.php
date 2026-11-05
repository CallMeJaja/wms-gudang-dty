<?php

namespace Database\Seeders;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\MutasiStok;
use App\Models\Satuan;
use App\Models\StokBarang;
use App\Models\StokOpname;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use App\Services\PenerimaanService;
use App\Services\PengeluaranService;
use App\Services\RilisBufferService;
use App\Services\StokOpnameService;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoTransaksiSeeder extends Seeder
{
    public const OPERATOR_NAMA = 'Budi Santoso';

    public const MERGE_POY = '4P1065';

    public const MERGE_POY_ALT = '4P1044';

    public const MERGE_DTY = '4D9160';

    public const MERGE_PENDUKUNG = 'GRN-VELVET';

    private const MERGE_DTY_LIST = ['4D9160', '4D9162', '4D9163'];

    private const GRADE_DTY_SIKLUS = ['AX', 'A', 'AX', 'B', 'AX'];

    private const SOPIR = ['Joko Demo', 'Slamet Kirim', 'Andi Antar', 'Budi Angkut'];

    private const KENDARAAN = ['B 1234 DEMO', 'B 5678 KIRIM', 'B 9012 ANTAR', 'B 3456 ANGKUT'];

    public function run(): void
    {
        $operator = User::query()->where('username', 'operator')->firstOrFail();
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $akhir = $this->tanggalBasis()->startOfDay();
        $awal = $akhir->copy()->subDays(29)->startOfDay();
        $userId = (int) $operator->id;

        $poy = Barang::query()->where('kode_barang', 'POY-250-48')->firstOrFail();
        $poy->update(['is_merge_minggu' => true]);

        $tanggalList = [];
        for ($d = $awal->copy(); $d->lte($akhir); $d->addDay()) {
            $tanggalList[] = $d->copy();
        }

        $rabus = array_values(array_filter($tanggalList, fn (Carbon $d): bool => (int) $d->format('N') === 3));
        $pendek = [];
        foreach (array_slice($rabus, 1, 2) as $d) {
            $pendek[] = $d->toDateString();
        }

        $senins = array_values(array_filter($tanggalList, fn (Carbon $d): bool => (int) $d->format('N') === 1));
        $gradeC = [];
        foreach (array_slice($senins, 1, 2) as $d) {
            $gradeC[] = $d->toDateString();
        }

        $kamis = array_values(array_filter($tanggalList, fn (Carbon $d): bool => (int) $d->format('N') === 4));
        $gpDates = array_slice($kamis, 0, 4);

        $noPoyIn = 1;
        $noPoyOut = 1;
        $noDtyIn = 1;
        $noSj = 0;
        $noGp = 0;

        foreach ($tanggalList as $i => $tgl) {
            $tglStr = $tgl->toDateString();
            $kerja = (int) $tgl->format('N') !== 7;
            $shift = ($i % 3) + 1;

            if ($kerja) {
                $this->penerimaanPoyHarian($poy, $tgl, $noPoyIn++, in_array($tglStr, $pendek, true), $shift, $userId);
            }

            $this->penerimaanDtyHarian($tgl, $noDtyIn++, $i, in_array($tglStr, $gradeC, true), $shift, $userId);

            foreach ($gpDates as $gi => $gd) {
                if ($gd->toDateString() === $tglStr) {
                    $this->penerimaanPendukungMingguan($tgl, $noGp++, $gi, $userId);
                }
            }

            if (! $kerja && ! $this->sudahRilis($tgl)) {
                app(RilisBufferService::class)->rilis($tgl, $userId);
            }

            $this->pengeluaranPoyHarian($poy, $tgl, $noPoyOut++, in_array($tglStr, $pendek, true), $kerja, $shift, $userId);

            $this->suratJalanHarian($tgl, $noSj++, $userId);
        }

        $this->penerimaanPoyHarian($poy, $akhir, $noPoyIn++, false, 1, $userId);

        $this->drafPoy($userId);
        $this->drafTambahan($userId);
        $this->opnameDemo((int) $admin->id, $akhir);
    }

    private function sudahRilis(Carbon $tgl): bool
    {
        return MutasiStok::query()
            ->where('jenis_transaksi', 'RILIS_BUFFER')
            ->where('ref_nomor', sprintf('RILIS-BUFFER-%s', $tgl->toDateString()))
            ->exists();
    }

    private function tanggalBasis(): Carbon
    {
        $tgl = today();

        return (int) $tgl->format('N') === 7 ? $tgl->addDay() : $tgl;
    }

    private function mergePoyMingguan(Carbon $tgl): string
    {
        return ((int) $tgl->format('W') % 2 === 0) ? self::MERGE_POY : self::MERGE_POY_ALT;
    }

    private function gradePoyMingguan(Carbon $tgl): string
    {
        return ((int) $tgl->format('W') % 2 === 0) ? 'AL' : 'Am';
    }

    private function mergeDtyMingguan(Carbon $tgl): string
    {
        return self::MERGE_DTY_LIST[(int) $tgl->format('W') % 3];
    }

    private function penerimaanPoyHarian(Barang $poy, Carbon $tgl, int $nomor, bool $pendek, int $shift, int $userId): void
    {
        $grade = $this->grade(KategoriBarang::POY, $this->gradePoyMingguan($tgl));
        $ipci = SupplierCustomer::query()->where('kode', 'IPCI')->firstOrFail();
        $pallet = $pendek ? 5 : (int) config('wms.operasional.pasokan_pallet_hari');
        $berat = $pallet * (float) config('wms.pallet.poy_kg');
        $bobbin = $pallet * (int) config('wms.satuan.poy_pallet_bobbin');

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => sprintf('DEMO-POY-IN-%02d', $nomor)],
            [
                'jenis_masuk' => JenisMasuk::PoyIpci,
                'tanggal' => $tgl,
                'partner_id' => $ipci->id,
                'shift' => $shift,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $poy->id,
                'merge' => $this->mergePoyMingguan($tgl),
                'grade_id' => $grade->id,
                'jumlah_bobbin' => $bobbin,
                'berat_kg' => $berat,
            ]);
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PenerimaanService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function pengeluaranPoyHarian(Barang $poy, Carbon $tgl, int $nomor, bool $pendek, bool $kerja, int $shift, int $userId): void
    {
        $merge = $this->mergePoyMingguan($tgl);
        $grade = $this->grade(KategoriBarang::POY, $this->gradePoyMingguan($tgl));
        $kgPerPallet = (float) config('wms.pallet.poy_kg');

        if ($kerja) {
            $berat = ($pendek ? 5 : (int) config('wms.operasional.konsumsi_pallet_hari')) * $kgPerPallet;
        } else {
            $saldo = app(StokService::class)->saldo($poy, $merge, $grade->id);
            $aktif = $saldo === null ? 0.0 : (float) $saldo->stok_aktif;
            $berat = min((int) config('wms.operasional.konsumsi_pallet_hari') * $kgPerPallet, $aktif);
            if ($berat < 1) {
                return;
            }
        }

        $trx = TransaksiKeluar::query()->firstOrCreate(
            ['nomor_transaksi' => sprintf('DEMO-POY-OUT-%02d', $nomor)],
            [
                'jenis_keluar' => JenisKeluar::PoyProduksi,
                'tanggal' => $tgl,
                'shift' => $shift,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $trx->detail()->create([
                'barang_id' => $poy->id,
                'merge' => $merge,
                'grade_id' => $grade->id,
                'berat_netto_kg' => $berat,
            ]);
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PengeluaranService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function penerimaanDtyHarian(Carbon $tgl, int $nomor, int $indeks, bool $isGradeC, int $shift, int $userId): void
    {
        $dty = Barang::query()->where('kode_barang', 'DTY-150-48-1')->firstOrFail();
        $kodeGrade = $isGradeC ? 'C' : self::GRADE_DTY_SIKLUS[$indeks % count(self::GRADE_DTY_SIKLUS)];
        $grade = $this->grade(KategoriBarang::DTY, $kodeGrade);
        $box = Satuan::query()->where('kode', 'BOX')->firstOrFail();
        $jumlahBox = $isGradeC ? 10 : 100 + ($indeks % 3);
        $merge = $this->mergeDtyMingguan($tgl);

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => sprintf('DEMO-DTY-IN-%02d', $nomor)],
            [
                'jenis_masuk' => JenisMasuk::DtyProduksi,
                'tanggal' => $tgl,
                'sumber_internal' => (string) config('wms.inbound.sumber_internal_dty'),
                'shift' => $shift,
                'operator_nama' => self::OPERATOR_NAMA,
                'merge_poy_asal' => $this->mergePoyMingguan($tgl),
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $daftarDetail = $isGradeC
                ? [
                    [$grade->id, 10],
                    [$this->grade(KategoriBarang::DTY, self::GRADE_DTY_SIKLUS[$indeks % count(self::GRADE_DTY_SIKLUS)])->id, 90 + ($indeks % 3)],
                ]
                : [[$grade->id, $jumlahBox]];

            foreach ($daftarDetail as [$gradeId, $boxQty]) {
                $netto = round($boxQty * (float) config('wms.satuan.dty_box_netto_kg'), 2);
                $gross = round($boxQty * (float) config('wms.satuan.dty_box_gross_kg'), 2);

                $trx->detail()->create([
                    'barang_id' => $dty->id,
                    'merge' => $merge,
                    'grade_id' => $gradeId,
                    'jumlah_kemasan' => $boxQty,
                    'satuan_kemasan_id' => $box->id,
                    'jumlah_bobbin' => (int) ($boxQty * (float) config('wms.satuan.dty_box_bobbin')),
                    'berat_kg' => $netto,
                    'keterangan' => TransaksiMasukDetail::penandaGross($gross),
                ]);
            }
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PenerimaanService::class)->posting($trx->fresh(), $userId);
        }
    }

    private function suratJalanHarian(Carbon $tgl, int $indeks, int $userId): void
    {
        $dty = Barang::query()->where('kode_barang', 'DTY-150-48-1')->firstOrFail();
        $ipci = SupplierCustomer::query()->where('kode', 'IPCI')->firstOrFail();
        $nettoPerBox = (float) config('wms.satuan.dty_box_netto_kg');
        $grossPerBox = (float) config('wms.satuan.dty_box_gross_kg');
        $bobbinPerBox = (float) config('wms.satuan.dty_box_bobbin');

        $jumlahBox = 100;

        $trx = TransaksiKeluar::query()->firstOrCreate(
            ['nomor_transaksi' => sprintf('DEMO-DTY-SJ-%02d', $indeks + 1)],
            [
                'jenis_keluar' => JenisKeluar::DtySuratJalan,
                'tanggal' => $tgl,
                'partner_id' => $ipci->id,
                'no_kendaraan' => self::KENDARAAN[$indeks % count(self::KENDARAAN)],
                'nama_sopir' => self::SOPIR[$indeks % count(self::SOPIR)],
                'pic_pengirim' => self::OPERATOR_NAMA,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($trx->detail()->count() === 0) {
            $sisaBox = $jumlahBox;

            $posisiList = StokBarang::query()
                ->whereHas('barang', fn ($q) => $q->whereHas('kategori', fn ($k) => $k->where('kode', KategoriBarang::DTY)))
                ->where('stok_aktif', '>', 0)
                ->orderByDesc('stok_aktif')
                ->get();

            foreach ($posisiList as $posisi) {
                if ($sisaBox <= 0) {
                    break;
                }

                $maksBox = (int) floor((float) $posisi->stok_aktif / $nettoPerBox);
                $ambilBox = min($maksBox, $sisaBox);

                if ($ambilBox <= 0) {
                    continue;
                }

                $trx->detail()->create([
                    'barang_id' => $dty->id,
                    'merge' => $posisi->merge,
                    'grade_id' => $posisi->grade_id,
                    'jumlah_kemasan' => $ambilBox,
                    'jumlah_bobbin' => (int) ($ambilBox * $bobbinPerBox),
                    'berat_netto_kg' => round($ambilBox * $nettoPerBox, 2),
                    'berat_gross_kg' => round($ambilBox * $grossPerBox, 2),
                ]);

                $sisaBox -= $ambilBox;
            }
        }

        if ($trx->fresh()->status === StatusDokumen::Draft) {
            app(PengeluaranService::class)->issue($trx->fresh(), $userId);
        }
    }

    private function penerimaanPendukungMingguan(Carbon $tgl, int $nomor, int $indeks, int $userId): void
    {
        $grade = $this->grade(KategoriBarang::PENDUKUNG, (string) config('wms.grade_pendukung_default'));
        $pcs = Satuan::query()->where('kode', 'PCS')->firstOrFail();
        $kg = Satuan::query()->where('kode', 'KG')->firstOrFail();
        $store = SupplierCustomer::query()->where('kode', 'STORE-IPCI')->firstOrFail();
        $tube = Barang::query()->where('kode_barang', '5003301227')->firstOrFail();
        $karton = Barang::query()->where('kode_barang', '5000002512')->firstOrFail();
        $oil = Barang::query()->where('kode_barang', '1003000201')->firstOrFail();

        $paket = match ($indeks % 4) {
            0 => [[$tube->id, 3456, $pcs->id], [$karton->id, 400, $pcs->id]],
            1 => [[$oil->id, 200, $kg->id]],
            2 => [[$tube->id, 2500, $pcs->id]],
            default => [[$karton->id, 450, $pcs->id]],
        };
        $batch = $indeks === 0 ? self::MERGE_PENDUKUNG : sprintf('GRN-PEI-%02d', $indeks);

        $trx = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => sprintf('DEMO-PENDUKUNG-IN-%02d', $nomor + 1)],
            [
                'jenis_masuk' => JenisMasuk::PendukungGatepass,
                'nomor_gate_pass' => sprintf('DEMO-GP-%03d', $nomor + 1),
                'tanggal' => $tgl,
                'partner_id' => $store->id,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        foreach ($paket as [$barangId, $qty, $satuanId]) {
            if ($trx->detail()->where('barang_id', $barangId)->count() === 0) {
                $trx->detail()->create([
                    'barang_id' => $barangId,
                    'merge' => '-',
                    'batch' => $batch,
                    'grade_id' => $grade->id,
                    'jumlah_kemasan' => $qty,
                    'satuan_kemasan_id' => $satuanId,
                    'berat_kg' => $qty,
                    'keterangan' => null,
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

    private function drafTambahan(int $userId): void
    {
        $poy = Barang::query()->where('kode_barang', 'POY-250-48')->firstOrFail();
        $dty = Barang::query()->where('kode_barang', 'DTY-150-48-1')->firstOrFail();
        $gradePoy = $this->grade(KategoriBarang::POY, 'Am');
        $gradeDty = $this->grade(KategoriBarang::DTY, 'AX');
        $ipci = SupplierCustomer::query()->where('kode', 'IPCI')->firstOrFail();

        $masuk = TransaksiMasuk::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-POY-DRAFT-02'],
            [
                'jenis_masuk' => JenisMasuk::PoyIpci,
                'tanggal' => today(),
                'partner_id' => $ipci->id,
                'shift' => 3,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($masuk->detail()->count() === 0) {
            $masuk->detail()->create([
                'barang_id' => $poy->id,
                'merge' => '4P1067',
                'grade_id' => $gradePoy->id,
                'berat_kg' => (float) config('wms.pallet.poy_kg'),
            ]);
        }

        $keluar = TransaksiKeluar::query()->firstOrCreate(
            ['nomor_transaksi' => 'DEMO-DTY-DRAFT-01'],
            [
                'jenis_keluar' => JenisKeluar::DtySuratJalan,
                'tanggal' => today(),
                'partner_id' => $ipci->id,
                'no_kendaraan' => 'B 9999 DRAF',
                'nama_sopir' => 'Sopir Draf',
                'pic_pengirim' => self::OPERATOR_NAMA,
                'operator_nama' => self::OPERATOR_NAMA,
                'status' => StatusDokumen::Draft,
                'created_by' => $userId,
            ]
        );

        if ($keluar->detail()->count() === 0) {
            $keluar->detail()->create([
                'barang_id' => $dty->id,
                'merge' => '4D9169',
                'grade_id' => $gradeDty->id,
                'jumlah_kemasan' => 10,
                'jumlah_bobbin' => (int) (10 * (float) config('wms.satuan.dty_box_bobbin')),
                'berat_netto_kg' => round(10 * (float) config('wms.satuan.dty_box_netto_kg'), 2),
                'berat_gross_kg' => round(10 * (float) config('wms.satuan.dty_box_gross_kg'), 2),
            ]);
        }
    }

    private function opnameDemo(int $adminId, Carbon $akhir): void
    {
        $grade = $this->grade(KategoriBarang::PENDUKUNG, (string) config('wms.grade_pendukung_default'));
        $tube = Barang::query()->where('kode_barang', '5003301227')->firstOrFail();
        $karton = Barang::query()->where('kode_barang', '5000002512')->firstOrFail();
        $oil = Barang::query()->where('kode_barang', '1003000201')->firstOrFail();

        $tanggalOpname = $akhir->copy()->endOfMonth()->toDateString();

        $this->opnameSatu($karton, self::MERGE_PENDUKUNG, $grade->id, 15, $tanggalOpname, $adminId, 'Opname demo selisih positif.');
        $this->opnameSatu($oil, 'GRN-PEI-01', $grade->id, -5, $tanggalOpname, $adminId, 'Opname demo selisih negatif kecil.');
        $this->opnameSatu($tube, self::MERGE_PENDUKUNG, $grade->id, 0, $tanggalOpname, $adminId, 'Opname demo selisih nol.');
    }

    private function opnameSatu(Barang $barang, string $merge, int $gradeId, float $selisih, string $tgl, int $adminId, string $keterangan): void
    {
        if ($barang->kategori?->kode === KategoriBarang::PENDUKUNG) {
            $merge = '-';
        }

        $sudah = StokOpname::query()
            ->where('barang_id', $barang->id)
            ->where('merge', $merge)
            ->whereDate('tanggal', $tgl)
            ->exists();

        if ($sudah) {
            return;
        }

        $saldo = app(StokService::class)->saldo($barang, $merge, $gradeId);
        $sistem = $saldo === null ? 0.0 : round((float) $saldo->stok_aktif + (float) $saldo->stok_buffer, 2);

        app(StokOpnameService::class)->proses(
            $barang, $merge, $gradeId,
            max(0, $sistem + $selisih), $tgl, $adminId, $keterangan,
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
