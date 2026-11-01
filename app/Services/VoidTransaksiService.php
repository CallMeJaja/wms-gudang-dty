<?php

namespace App\Services;

use App\Enums\JenisMutasi;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Models\ActivityLog;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pembatalan (void) transaksi yang sudah mempengaruhi stok (PRD §4.6, §7.5, §7.6).
 *
 * Prinsip: VOID TIDAK menghapus transaksi (audit trail utuh), hanya membalik
 * mutasi terkait dan mengubah status menjadi VOID beserta alasan wajib.
 *
 * STRATEGI VOID PENERIMAAN (inti desain F6):
 *  - Saat posting, seluruh qty detail ditambahkan ke pos AKTIF lebih dulu,
 *    baru sebagian dipindah ke BUFFER via alokasi (lihat AlokasiBufferService:
 *    "kg penerimaan WAJIB sudah ditambahkan ke AKTIF SEBELUM alokasi").
 *  - Karena itu void TIDAK membalik mutasi per baris (MASUK / ALOKASI_BUFFER /
 *    RILIS_BUFFER satu per satu). Sebaliknya, dua langkah per detail:
 *      1. Bila alokasi buffer transaksi ini BELUM dirilis (buffer > 0),
 *         kembalikan dulu ke AKTIF via RILIS_BUFFER. Void membatalkan
 *         penerimaan PENUH — tidak boleh ditolak hanya karena sebagian
 *         stok sedang dikunci sebagai buffer.
 *      2. Kurangi AKTIF sebesar total qty detail.
 *    Benar pada kedua kondisi buffer:
 *      * Buffer belum dirilis: buffer dikembalikan ke aktif, lalu aktif
 *        dikurangi total qty — saldo akhir nol, tanpa minus di pos mana pun.
 *      * Buffer sudah dirilis: buffer = 0, aktif = total qty — pengurangan
 *        AKTIF sebesar total qty sudah tepat.
 *  - totalBufferMinggu() membaca `stok_buffer`, sehingga setelah rilis
 *    internal ia konsisten (buffer transaksi yang di-void = 0).
 *  - Baris `alokasi_buffer` terkait TIDAK dihapus (jejak kebijakan buffer utuh),
 *    hanya diberi penanda "Dibatalkan via void {nomor}" pada `keterangan` agar
 *    tidak menggantung menunjuk transaksi VOID tanpa penjelasan. Kolom
 *    `jumlah_kg`/`dilewati` dibiarkan apa adanya untuk audit.
 *  - Bila `stok_aktif` tidak cukup (sebagian sudah dipakai produksi /
 *    dikeluarkan), void DITOLAK via StokTidakCukupException dari
 *    StokService::kurangiStok — saldo tidak boleh minus, seluruh transaction
 *    rollback dan status tetap POSTED/ISSUED.
 *
 * Pengaman konkurensi mengikuti pola posting (PenerimaanService): seluruh
 * pipeline di DB::transaction + lockForUpdate() baris header + cek ulang
 * status di dalam lock, sehingga void ganda hanya lolos satu kali.
 */
class VoidTransaksiService
{
    /**
     * Panjang minimal alasan void agar bermakna untuk audit.
     * Bukan angka bisnis stok — ambang validasi input formulir.
     */
    public const MIN_ALASAN = 10;

    public function __construct(private readonly StokService $stokService) {}

    /**
     * Batalkan transaksi masuk POSTED/ISSUED -> VOID.
     *
     * @throws AturanBisnisException bila bukan admin, status bukan
     *                               POSTED/ISSUED, alasan kosong/terlalu pendek,
     *                               atau stok aktif tidak cukup (StokTidakCukupException).
     */
    public function voidTransaksiMasuk(TransaksiMasuk $transaksi, string $alasan, int $userId): TransaksiMasuk
    {
        $alasan = trim($alasan);

        $this->validasiAdmin($userId);
        $this->validasiAlasan($alasan);
        $this->validasiStatus($transaksi, [StatusDokumen::Posted, StatusDokumen::Issued]);

        return DB::transaction(function () use ($transaksi, $alasan, $userId): TransaksiMasuk {
            $terkunci = TransaksiMasuk::query()
                ->lockForUpdate()
                ->findOrFail($transaksi->getKey());

            $this->validasiStatus($terkunci, [StatusDokumen::Posted, StatusDokumen::Issued]);

            $detailList = $terkunci->detail()->with('barang')->get();

            foreach ($detailList as $detail) {
                $barang = $detail->barang;
                $merge = (string) $detail->merge;
                $gradeId = (int) $detail->grade_id;
                $qty = (float) $detail->berat_kg;
                $refNomor = (string) $terkunci->nomor_transaksi;

                $barisStok = $this->stokService->saldo($barang, $merge, $gradeId);
                $bufferTerkait = $barisStok === null ? 0.0 : (float) $barisStok->stok_buffer;

                // Langkah 1 — bila alokasi buffer transaksi ini BELUM dirilis
                // (buffer masih tersimpan), kembalikan dulu ke AKTIF via
                // RILIS_BUFFER agar total fisik utuh sebelum dikurangi.
                // Void HARUS membatalkan penerimaan penuh, bukan ditolak hanya
                // karena sebagian stok sedang dikunci sebagai buffer.
                if ($bufferTerkait > 0) {
                    $this->stokService->rilisBuffer(
                        barang: $barang,
                        merge: $merge,
                        gradeId: $gradeId,
                        qty: $bufferTerkait,
                        refNomor: $refNomor,
                        userId: $userId,
                    );
                }

                // Langkah 2 — kurangi AKTIF sebesar total qty penerimaan.
                // Bila sebagian sudah dipakai produksi, void ditolak di sini.
                $this->stokService->kurangiStok(
                    barang: $barang,
                    merge: $merge,
                    gradeId: $gradeId,
                    qty: $qty,
                    pos: PosStok::Aktif,
                    jenisMutasi: JenisMutasi::Void,
                    refNomor: $refNomor,
                    userId: $userId,
                );
            }

            foreach ($terkunci->alokasiBuffer()->lockForUpdate()->get() as $alokasi) {
                $penanda = 'Dibatalkan via void '.$terkunci->nomor_transaksi;
                $catatan = trim((string) $alokasi->keterangan);

                if (! str_contains($catatan, $penanda)) {
                    $alokasi->keterangan = mb_substr(trim($catatan.' | '.$penanda, ' |'), 0, 255);
                    $alokasi->save();
                }
            }

            $terkunci->status = StatusDokumen::Void;
            $terkunci->alasan_void = $alasan;
            $terkunci->voided_at = now();
            $terkunci->voided_by = $userId;
            $terkunci->save();

            ActivityLog::catat(
                'VOID',
                'TRANSAKSI_MASUK',
                (int) $terkunci->id,
                sprintf(
                    'Void %s (%s kg, %d baris) — %s.',
                    $terkunci->nomor_transaksi,
                    number_format($detailList->sum(fn ($d) => (float) $d->berat_kg), 2, ',', '.'),
                    $detailList->count(),
                    $alasan,
                ),
                ['user_id' => $userId],
            );

            $terkunci->refresh();

            return $terkunci;
        });
    }

    /**
     * Batalkan transaksi keluar POSTED/ISSUED -> VOID (cerminan penerimaan).
     *
     * Keluar mengurangi AKTIF saat posting (F3), sehingga void MENAMBAH
     * kembali AKTIF sebesar total netto detail. Penambahan tidak pernah gagal
     * karena kuota, jadi tidak ada penolakan stok di sini. Disiapkan lebih
     * awal agar F3 memakai kontrak yang sama; F3 tinggal memanggil.
     *
     * @throws AturanBisnisException bila bukan admin, status bukan
     *                               POSTED/ISSUED, atau alasan tidak valid.
     */
    public function voidTransaksiKeluar(TransaksiKeluar $transaksi, string $alasan, int $userId): TransaksiKeluar
    {
        $alasan = trim($alasan);

        $this->validasiAdmin($userId);
        $this->validasiAlasan($alasan);
        $this->validasiStatus($transaksi, [StatusDokumen::Posted, StatusDokumen::Issued]);

        return DB::transaction(function () use ($transaksi, $alasan, $userId): TransaksiKeluar {
            $terkunci = TransaksiKeluar::query()
                ->lockForUpdate()
                ->findOrFail($transaksi->getKey());

            $this->validasiStatus($terkunci, [StatusDokumen::Posted, StatusDokumen::Issued]);

            $detailList = $terkunci->detail()->with('barang')->get();

            foreach ($detailList as $detail) {
                $this->stokService->tambahStok(
                    barang: $detail->barang,
                    merge: (string) $detail->merge,
                    gradeId: (int) $detail->grade_id,
                    qty: (float) $detail->berat_netto_kg,
                    pos: PosStok::Aktif,
                    jenisMutasi: JenisMutasi::Void,
                    refNomor: (string) $terkunci->nomor_transaksi,
                    userId: $userId,
                );
            }

            $terkunci->status = StatusDokumen::Void;
            $terkunci->alasan_void = $alasan;
            $terkunci->voided_at = now();
            $terkunci->voided_by = $userId;
            $terkunci->save();

            ActivityLog::catat(
                'VOID',
                'TRANSAKSI_KELUAR',
                (int) $terkunci->id,
                sprintf(
                    'Void %s (%s kg, %d baris) — %s.',
                    $terkunci->nomor_transaksi,
                    number_format($detailList->sum(fn ($d) => (float) $d->berat_netto_kg), 2, ',', '.'),
                    $detailList->count(),
                    $alasan,
                ),
                ['user_id' => $userId],
            );

            $terkunci->refresh();

            return $terkunci;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, StatusDokumen>  $boleh
     *
     * @throws AturanBisnisException
     */
    private function validasiStatus(TransaksiMasuk|TransaksiKeluar $transaksi, array $boleh): void
    {
        if (! in_array($transaksi->status, $boleh, true)
            || ! $transaksi->status->canTransitionTo(StatusDokumen::Void)) {
            throw new AturanBisnisException(
                "Transaksi {$transaksi->nomor_transaksi} berstatus {$transaksi->status->value} "
                .'dan tidak dapat di-void. Hanya POSTED/ISSUED yang dapat dibatalkan.'
            );
        }
    }

    /**
     * @throws AturanBisnisException
     */
    private function validasiAdmin(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if (! $user->canManage()) {
            throw new AturanBisnisException('Hanya Admin Gudang yang boleh membatalkan (void) transaksi.');
        }
    }

    /**
     * @throws AturanBisnisException
     */
    private function validasiAlasan(string $alasan): void
    {
        if ($alasan === '') {
            throw new AturanBisnisException('Alasan pembatalan (void) wajib diisi.');
        }

        if (mb_strlen($alasan) < self::MIN_ALASAN) {
            throw new AturanBisnisException(
                'Alasan void minimal '.self::MIN_ALASAN.' karakter agar bermakna untuk audit.'
            );
        }
    }
}
