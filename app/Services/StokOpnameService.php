<?php

namespace App\Services;

use App\Enums\JenisKeluar;
use App\Enums\JenisMasuk;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\MutasiStok;
use App\Models\NomorDokumen;
use App\Models\StokBarang;
use App\Models\StokOpname;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiKeluarDetail;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StokOpnameService
{
    public function __construct(
        private readonly StokService $stokService,
        private readonly NomorDokumenService $nomor,
    ) {}

    public function proses(
        Barang $barang,
        string $merge,
        int $gradeId,
        float $stokFisik,
        string $tanggal,
        int $userId,
        ?string $keterangan = null,
    ): StokOpname {
        return DB::transaction(function () use (
            $barang, $merge, $gradeId, $stokFisik, $tanggal, $userId, $keterangan
        ): StokOpname {
            $this->validasiAdmin($userId);

            if ($barang->kategori?->kode === KategoriBarang::PENDUKUNG) {
                $merge = '-';
                $gradeId = $this->gradePendukungId($barang);
            } else {
                $merge = trim($merge);

                if ($merge === '') {
                    throw new AturanBisnisException('Merge wajib diisi untuk opname POY/DTY.');
                }
            }

            if ($stokFisik < 0) {
                throw new AturanBisnisException('Stok fisik tidak boleh negatif.');
            }

            try {
                $tgl = Carbon::parse($tanggal)->toDateString();
            } catch (\Throwable) {
                throw new AturanBisnisException('Tanggal opname tidak valid.');
            }

            if (Carbon::parse($tgl)->endOfMonth()->toDateString() !== $tgl) {
                throw new AturanBisnisException('Opname stok hanya boleh dilakukan pada hari terakhir bulan.');
            }

            $sesuai = Grade::query()
                ->whereKey($gradeId)
                ->where('kategori_id', $barang->kategori_id)
                ->exists();

            if (! $sesuai) {
                throw AturanBisnisException::gradeTidakSesuaiKategori((string) $barang->kode_barang);
            }

            if ($tgl < now()->toDateString() && MutasiStok::query()
                ->where('barang_id', $barang->id)
                ->where('merge', $merge)
                ->where('grade_id', $gradeId)
                ->whereDate('tanggal', '>', $tgl)
                ->exists()) {
                throw new AturanBisnisException(
                    'Opname tanggal mundur tidak diizinkan karena sudah ada mutasi stok setelah tanggal tersebut.'
                );
            }

            StokBarang::firstOrCreate(
                ['barang_id' => $barang->id, 'merge' => $merge, 'grade_id' => $gradeId],
                ['stok_aktif' => 0, 'stok_buffer' => 0],
            );

            $stok = StokBarang::query()
                ->posisi($barang->id, $merge, $gradeId)
                ->lockForUpdate()
                ->firstOrFail();

            $aktif = (float) $stok->stok_aktif;
            $sistem = round($aktif + (float) $stok->stok_buffer, 2);
            $selisih = round($stokFisik - $sistem, 2);

            $duplikat = StokOpname::query()
                ->where('barang_id', $barang->id)
                ->where('merge', $merge)
                ->where('grade_id', $gradeId)
                ->whereDate('tanggal', $tgl)
                ->exists();

            if ($duplikat) {
                throw new AturanBisnisException('Posisi ini sudah diopname pada tanggal tersebut.');
            }

            if ($selisih > 0) {
                $nomorTrx = $this->nomor->generateUnik(
                    NomorDokumen::TRX_MASUK,
                    fn (string $nomor): bool => TransaksiMasuk::query()->where('nomor_transaksi', $nomor)->exists(),
                );
                $opname = $this->simpanOpname($barang, $merge, $gradeId, $sistem, $stokFisik, $tgl, $userId, $keterangan, 'OPN-'.$nomorTrx);

                $trx = TransaksiMasuk::create([
                    'nomor_transaksi' => $nomorTrx,
                    'jenis_masuk' => JenisMasuk::AdjustmentIn,
                    'tanggal' => $tgl,
                    'catatan' => $keterangan,
                    'status' => StatusDokumen::Posted,
                    'created_by' => $userId,
                ]);

                TransaksiMasukDetail::create([
                    'transaksi_masuk_id' => $trx->id,
                    'barang_id' => $barang->id,
                    'merge' => $merge,
                    'grade_id' => $gradeId,
                    'berat_kg' => $selisih,
                ]);

                $this->stokService->tambahStok(
                    barang: $barang,
                    merge: $merge,
                    gradeId: $gradeId,
                    qty: $selisih,
                    pos: PosStok::Aktif,
                    jenisMutasi: JenisMutasi::AdjustmentIn,
                    refNomor: $nomorTrx,
                    userId: $userId,
                    tanggal: Carbon::parse($tgl),
                );

                $opname->transaksi_masuk_id = $trx->id;
                $opname->save();
            } elseif ($selisih < 0) {
                $kurang = abs($selisih);
                $nomorTrx = $this->nomor->generateUnik(
                    NomorDokumen::TRX_KELUAR,
                    fn (string $nomor): bool => TransaksiKeluar::query()->where('nomor_transaksi', $nomor)->exists(),
                );
                $opname = $this->simpanOpname($barang, $merge, $gradeId, $sistem, $stokFisik, $tgl, $userId, $keterangan, 'OPN-'.$nomorTrx);

                $trx = TransaksiKeluar::create([
                    'nomor_transaksi' => $nomorTrx,
                    'jenis_keluar' => JenisKeluar::AdjustmentOut,
                    'tanggal' => $tgl,
                    'catatan' => $keterangan,
                    'status' => StatusDokumen::Posted,
                    'created_by' => $userId,
                ]);

                TransaksiKeluarDetail::create([
                    'transaksi_keluar_id' => $trx->id,
                    'barang_id' => $barang->id,
                    'merge' => $merge,
                    'grade_id' => $gradeId,
                    'berat_netto_kg' => $kurang,
                ]);

                $dariAktif = min($aktif, $kurang);
                $dariBuffer = round($kurang - $dariAktif, 2);
                $tglCarbon = Carbon::parse($tgl);

                if ($dariAktif > 0) {
                    $this->stokService->kurangiStok(
                        barang: $barang,
                        merge: $merge,
                        gradeId: $gradeId,
                        qty: $dariAktif,
                        pos: PosStok::Aktif,
                        jenisMutasi: JenisMutasi::AdjustmentOut,
                        refNomor: $nomorTrx,
                        userId: $userId,
                        tanggal: $tglCarbon,
                    );
                }

                if ($dariBuffer > 0) {
                    $this->stokService->kurangiStok(
                        barang: $barang,
                        merge: $merge,
                        gradeId: $gradeId,
                        qty: $dariBuffer,
                        pos: PosStok::Buffer,
                        jenisMutasi: JenisMutasi::AdjustmentOut,
                        refNomor: $nomorTrx,
                        userId: $userId,
                        tanggal: $tglCarbon,
                    );
                }

                $opname->transaksi_keluar_id = $trx->id;
                $opname->save();
            } else {
                $bersih = substr((string) preg_replace('/[^A-Za-z0-9]/', '', $merge), 0, 12);
                $nomorOpname = sprintf(
                    'OPN-%s-%d-%d-%s-%s',
                    Carbon::parse($tgl)->format('Ymd'),
                    $barang->id,
                    $gradeId,
                    $bersih === '' ? 'X' : $bersih,
                    substr(md5($barang->id.'|'.$merge.'|'.$gradeId.'|'.$tgl), 0, 6),
                );
                $opname = $this->simpanOpname($barang, $merge, $gradeId, $sistem, $stokFisik, $tgl, $userId, $keterangan, $nomorOpname);
            }

            ActivityLog::catat(
                'CREATE',
                'STOK_OPNAME',
                (int) $opname->id,
                sprintf(
                    'Opname %s (%s kg, selisih %s kg) oleh user #%s.',
                    $opname->nomor_opname,
                    number_format($stokFisik, 2, ',', '.'),
                    number_format($selisih, 2, ',', '.'),
                    (string) $userId,
                ),
                ['user_id' => $userId],
            );

            return $opname->refresh();
        });
    }

    private function simpanOpname(
        Barang $barang,
        string $merge,
        int $gradeId,
        float $sistem,
        float $fisik,
        string $tgl,
        int $userId,
        ?string $keterangan,
        string $nomorOpname,
    ): StokOpname {
        try {
            return StokOpname::create([
                'nomor_opname' => $nomorOpname,
                'tanggal' => $tgl,
                'barang_id' => $barang->id,
                'merge' => $merge,
                'grade_id' => $gradeId,
                'stok_sistem_qty' => $sistem,
                'stok_fisik_qty' => $fisik,
                'keterangan' => $keterangan,
                'created_by' => $userId,
            ]);
        } catch (QueryException $e) {
            throw new AturanBisnisException('Posisi ini sudah diopname pada tanggal tersebut.');
        }
    }

    private function gradePendukungId(Barang $barang): int
    {
        $kode = (string) config('wms.grade_pendukung_default', 'STD');
        $gradeId = (int) Grade::query()
            ->where('kategori_id', $barang->kategori_id)
            ->where('kode', $kode)
            ->value('id');

        if ($gradeId === 0) {
            throw new AturanBisnisException("Grade netral '{$kode}' untuk kategori PENDUKUNG belum ada.");
        }

        return $gradeId;
    }

    private function validasiAdmin(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if (! $user->canManage()) {
            throw new AturanBisnisException('Hanya Admin Gudang yang boleh melakukan stock opname.');
        }
    }
}
