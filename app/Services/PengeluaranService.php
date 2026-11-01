<?php

namespace App\Services;

use App\Enums\JenisKeluar;
use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Exceptions\StokTidakCukupException;
use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiKeluarDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Orkestrasi transaksi keluar: DRAFT -> POSTED / ISSUED.
 *
 * Cerminan PenerimaanService untuk sisi keluar:
 *
 *   POSTING (POY_PRODUKSI / DTY_SCRAP_REJECT), target POSTED:
 *     1. validasi tiap detail (kategori, grade, merge, netto, stok aktif cukup)
 *     2. kurangiStok(detail, PosStok::AKTIF) untuk setiap baris detail
 *
 *   ISSUE (DTY_SURAT_JALAN), target ISSUED:
 *     1. generate nomor_surat_jalan bila kosong (atomik)
 *     2. sama seperti posting (kurangi stok AKTIF)
 *
 * Aturan kritis: berat keluar TIDAK BOLEH melebihi `stok_aktif`
 * per (barang+merge+grade). Buffer tidak boleh terpakai di hari kerja —
 * pesan penolakan memakai kalimat baku AturanBisnisException.
 *
 * Pengaman double-posting mengikuti pola posting: DB::transaction +
 * lockForUpdate() baris header + cek ulang status di dalam lock.
 */
class PengeluaranService
{
    public function __construct(
        private readonly StokService $stokService,
        private readonly NomorDokumenService $nomorService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Mapping jenis -> modul ActivityLog & kategori & status target
    |--------------------------------------------------------------------------
    */

    public static function modul(JenisKeluar $jenis): string
    {
        return match ($jenis) {
            JenisKeluar::PoyProduksi => 'TRANSAKSI_KELUAR_POY',
            JenisKeluar::DtySuratJalan => 'TRANSAKSI_KELUAR_SJ',
            JenisKeluar::DtyScrapReject => 'TRANSAKSI_KELUAR_SCRAP',
            default => 'TRANSAKSI_KELUAR',
        };
    }

    public static function kodeKategori(JenisKeluar $jenis): string
    {
        return match ($jenis) {
            JenisKeluar::PoyProduksi => KategoriBarang::POY,
            JenisKeluar::DtySuratJalan, JenisKeluar::DtyScrapReject => KategoriBarang::DTY,
            default => KategoriBarang::POY,
        };
    }

    public static function statusTarget(JenisKeluar $jenis): StatusDokumen
    {
        return $jenis->isDokumenResmi() ? StatusDokumen::Issued : StatusDokumen::Posted;
    }

    /*
    |--------------------------------------------------------------------------
    | POSTING / ISSUE
    |--------------------------------------------------------------------------
    */

    public function posting(TransaksiKeluar $transaksi, ?int $userId = null): StatusDokumen
    {
        return $this->keluarkan(
            $transaksi,
            [JenisKeluar::PoyProduksi, JenisKeluar::DtyScrapReject],
            StatusDokumen::Posted,
            $userId,
            false,
        );
    }

    public function issue(TransaksiKeluar $transaksi, ?int $userId = null): StatusDokumen
    {
        return $this->keluarkan(
            $transaksi,
            [JenisKeluar::DtySuratJalan],
            StatusDokumen::Issued,
            $userId,
            true,
        );
    }

    /**
     * @param  array<int, JenisKeluar>  $jenisBoleh
     */
    private function keluarkan(
        TransaksiKeluar $transaksi,
        array $jenisBoleh,
        StatusDokumen $target,
        ?int $userId,
        bool $generateNomorSj,
    ): StatusDokumen {
        return DB::transaction(function () use ($transaksi, $jenisBoleh, $target, $userId, $generateNomorSj): StatusDokumen {
            $terkunci = TransaksiKeluar::query()
                ->lockForUpdate()
                ->findOrFail($transaksi->getKey());

            if ($terkunci->status !== StatusDokumen::Draft) {
                throw new AturanBisnisException(
                    "Transaksi {$terkunci->nomor_transaksi} berstatus {$terkunci->status->value} "
                    .'dan tidak dapat diproses ulang. Hanya DRAFT yang dapat diposting.'
                );
            }

            if (! in_array($terkunci->jenis_keluar, $jenisBoleh, true)) {
                throw new AturanBisnisException(
                    "Transaksi {$terkunci->nomor_transaksi} berjenis {$terkunci->jenis_keluar->value} "
                    .'dan tidak dapat diproses pada aksi ini.'
                );
            }

            if (! $terkunci->status->canTransitionTo($target)) {
                throw new AturanBisnisException(
                    "Transisi status {$terkunci->status->value} -> {$target->value} tidak diizinkan."
                );
            }

            /** @var Collection<int, TransaksiKeluarDetail> $detailList */
            $detailList = $terkunci->detail()->with(['barang.kategori', 'grade'])->get();

            if ($detailList->isEmpty()) {
                throw new AturanBisnisException(
                    "Transaksi {$terkunci->nomor_transaksi} tidak memiliki baris detail — tidak dapat diproses."
                );
            }

            $this->validasiDetail($terkunci, $detailList);

            $userId ??= $terkunci->created_by !== null ? (int) $terkunci->created_by : null;

            if ($generateNomorSj && trim((string) $terkunci->nomor_surat_jalan) === '') {
                $terkunci->nomor_surat_jalan = $this->nomorService->generate('SURAT_JALAN_DTY');
                $terkunci->save();
            }

            foreach ($detailList as $detail) {
                try {
                    $this->stokService->kurangiStok(
                        barang: $detail->barang,
                        merge: (string) $detail->merge,
                        gradeId: (int) $detail->grade_id,
                        qty: (float) $detail->berat_netto_kg,
                        pos: PosStok::Aktif,
                        jenisMutasi: JenisMutasi::Keluar,
                        refNomor: (string) $terkunci->nomor_transaksi,
                        userId: $userId,
                        tanggal: $terkunci->tanggal,
                    );
                } catch (StokTidakCukupException) {
                    throw new AturanBisnisException(
                        AturanBisnisException::PESAN_BUFFER_TIDAK_BOLEH_DIPAKAI
                    );
                }
            }

            $terkunci->status = $target;
            $terkunci->save();

            ActivityLog::catat(
                'UPDATE',
                self::modul($terkunci->jenis_keluar),
                (int) $terkunci->id,
                sprintf(
                    '%s %s (%s kg, %d baris) oleh user #%s.',
                    $target === StatusDokumen::Issued ? 'Issue dokumen' : 'Posting transaksi',
                    $terkunci->nomor_transaksi,
                    number_format($detailList->sum(fn (TransaksiKeluarDetail $d) => (float) $d->berat_netto_kg), 2, ',', '.'),
                    $detailList->count(),
                    (string) ($userId ?? '-'),
                ),
                ['user_id' => $userId],
            );

            return $target;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi bisnis per jenis
    |--------------------------------------------------------------------------
    */

    /**
     * @param  Collection<int, TransaksiKeluarDetail>  $detailList
     *
     * @throws AturanBisnisException
     */
    public function validasiDetail(TransaksiKeluar $transaksi, Collection $detailList): void
    {
        if ($transaksi->jenis_keluar->requiresCatatan() && trim((string) $transaksi->catatan) === '') {
            throw new AturanBisnisException(
                "Transaksi {$transaksi->nomor_transaksi}: catatan wajib diisi (tujuan/penerima scrap)."
            );
        }

        $kodeKategori = self::kodeKategori($transaksi->jenis_keluar);

        foreach ($detailList as $urutan => $detail) {
            $label = 'Baris '.($urutan + 1);
            $barang = $detail->barang;

            if (! $barang instanceof Barang) {
                throw new AturanBisnisException("{$label}: barang tidak ditemukan.");
            }

            if ($barang->kategori?->kode !== $kodeKategori) {
                throw new AturanBisnisException(sprintf(
                    '%s: barang %s ber-kategori %s, tidak boleh dipakai pada transaksi %s (harus kategori %s).',
                    $label,
                    $barang->kode_barang,
                    (string) ($barang->kategori?->kode ?? '—'),
                    $transaksi->jenis_keluar->value,
                    $kodeKategori,
                ));
            }

            $grade = $detail->grade;

            if (! $grade instanceof Grade || (int) $grade->kategori_id !== (int) $barang->kategori_id) {
                throw AturanBisnisException::gradeTidakSesuaiKategori((string) $barang->kode_barang);
            }

            if (trim((string) $detail->merge) === '') {
                throw new AturanBisnisException("{$label}: merge wajib diisi.");
            }

            if ((float) $detail->berat_netto_kg <= 0) {
                throw new AturanBisnisException("{$label}: berat netto wajib diisi dan lebih besar dari 0.");
            }

            if ($detail->berat_gross_kg !== null && (float) $detail->berat_gross_kg < (float) $detail->berat_netto_kg) {
                throw new AturanBisnisException("{$label}: berat gross tidak boleh lebih kecil dari netto.");
            }

            $saldo = $this->stokService->saldo($barang, (string) $detail->merge, (int) $detail->grade_id);
            $aktif = $saldo === null ? 0.0 : (float) $saldo->stok_aktif;

            if ($aktif + 1e-9 < (float) $detail->berat_netto_kg) {
                throw new AturanBisnisException(
                    AturanBisnisException::PESAN_BUFFER_TIDAK_BOLEH_DIPAKAI
                );
            }
        }
    }

    public static function totalNetto(TransaksiKeluar $transaksi): float
    {
        return (float) $transaksi->detail->sum(fn (TransaksiKeluarDetail $detail) => (float) $detail->berat_netto_kg);
    }

    public static function totalGross(TransaksiKeluar $transaksi): float
    {
        return (float) $transaksi->detail->sum(fn (TransaksiKeluarDetail $detail) => (float) $detail->berat_gross_kg);
    }
}
