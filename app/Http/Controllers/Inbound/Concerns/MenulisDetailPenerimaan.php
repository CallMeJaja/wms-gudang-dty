<?php

namespace App\Http\Controllers\Inbound\Concerns;

use App\Enums\StatusDokumen;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Models\SupplierCustomer;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Services\KonversiSatuanService;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Bagian bersama ketiga controller penerimaan (F2B).
 *
 * Semua data master yang dibutuhkan form dikirim sebagai SATU payload JSON
 * (`wms-inbound.data`) di `<script type="application/json">` — bukan
 * `@json()` per baris — sehingga view/JS dapat merender opsi tanpa request
 * tambahan dan tetap aman dari injeksi (Blade meng-escape tag penutup).
 */
trait MenulisDetailPenerimaan
{
    /**
     * Simpan baris detail hasil validasi ke transaksi.
     *
     * Baris lama dihapus lalu ditulis ulang: transaksi di sini SELALU masih
     * DRAFT (policy + route tolak.manajer menjamin), sehingga penggantian
     * seluruh detail aman dan konsisten (idempotent untuk form dinamis).
     *
     * @param  array<int, array<string, mixed>>  $detail
     */
    protected function tulisDetail(
        TransaksiMasuk $transaksi,
        array $detail,
        KonversiSatuanService $konversi,
    ): void {
        $transaksi->detail()->delete();

        $idKg = Satuan::query()->where('kode', Satuan::KG)->value('id');
        $idBox = Satuan::query()->where('kode', PenerimaanService::SATUAN_BOX)->value('id');

        foreach ($detail as $baris) {
            $barang = Barang::query()->with('kategori')->findOrFail((int) $baris['barang_id']);
            $satuanId = isset($baris['satuan_kemasan_id']) ? (int) $baris['satuan_kemasan_id'] : null;
            $jumlahKemasan = isset($baris['jumlah_kemasan']) ? (float) $baris['jumlah_kemasan'] : null;

            // DTY/POY mengirim `jumlah_box`; skema menyimpan box pada
            // `jumlah_kemasan` + `satuan_kemasan_id` = BOX.
            if ($satuanId === null && isset($baris['jumlah_box']) && $baris['jumlah_box'] !== '') {
                $jumlahKemasan = (float) $baris['jumlah_box'];
                $satuanId = $idBox;
            }

            $data = [
                'barang_id' => $barang->id,
                'merge' => trim((string) $baris['merge']),
                'grade_id' => (int) $baris['grade_id'],
                'jumlah_kemasan' => $jumlahKemasan,
                'satuan_kemasan_id' => $satuanId,
                'jumlah_bobbin' => isset($baris['jumlah_bobbin']) && $baris['jumlah_bobbin'] !== ''
                    ? (int) $baris['jumlah_bobbin']
                    : null,
                'berat_kg' => round((float) $baris['berat_kg'], 2),
                'keterangan' => $this->keteranganBaris($baris),
            ];

            // Item pendukung: bila satuan kemasan == satuan dasar barang, qty
            // dipakai 1:1 (tidak ada konversi). Bila barang ber-satuan dasar KG
            // dan satuan kemasan berbeda, qty dikonversi via KonversiSatuanService.
            if ($satuanId === (int) $barang->satuan_dasar_id && $jumlahKemasan !== null) {
                // Satuan kemasan == satuan dasar: qty dipakai 1:1 (PCS/ROLL/KG).
                $data['berat_kg'] = round($jumlahKemasan, 2);
            } elseif ($satuanId !== null && $jumlahKemasan !== null) {
                $kg = $konversi->hitungKgDariKemasan($barang, $jumlahKemasan, $satuanId);

                if ($kg !== null && (int) $barang->satuan_dasar_id === (int) $idKg) {
                    $data['berat_kg'] = $kg;
                }
            }

            $transaksi->detail()->create($data);
        }
    }

    /**
     * Keterangan baris.
     *
     * - Item pendukung: isi `batch` dari dokumen Gate Pass (PRD §4.2).
     * - DTY: `batch`/catatan + penanda terstruktur `GROSS=<kg>` karena
     *   `transaksi_masuk_detail` tidak memiliki kolom berat_gross_kg.
     *
     * @param  array<string, mixed>  $baris
     */
    protected function keteranganBaris(array $baris): ?string
    {
        $teks = trim((string) ($baris['keterangan'] ?? ''));
        $gross = $baris['berat_gross_kg'] ?? null;

        if ($gross !== null && $gross !== '' && (float) $gross > 0) {
            $teks = trim($teks.' '.TransaksiMasukDetail::penandaGross((float) $gross));
        }

        return $teks === '' ? null : mb_substr($teks, 0, 255);
    }

    /**
     * Grade default untuk item pendukung.
     *
     * Item pendukung tidak punya grade pada dokumen Gate Pass, tetapi skema
     * `transaksi_masuk_detail.grade_id` NOT NULL. Dipakai satu grade netral
     * milik kategori PENDUKUNG (kode dari config('wms.grade_pendukung_default')).
     */
    protected function gradePendukung(): Grade
    {
        $kategoriId = KategoriBarang::query()
            ->where('kode', KategoriBarang::PENDUKUNG)
            ->value('id');

        abort_if($kategoriId === null, 500, 'Kategori PENDUKUNG belum ada di master data.');

        $kode = (string) config('wms.grade_pendukung_default', 'STD');

        $grade = Grade::query()
            ->where('kategori_id', $kategoriId)
            ->where('kode', $kode)
            ->first();

        // Grade tertutup (A7): tidak dibuat on-the-fly, wajib berasal dari seeder.
        abort_if(
            $grade === null,
            500,
            "Grade '{$kode}' untuk kategori PENDUKUNG belum di-seed. Jalankan: php artisan db:seed --class=GradeSeeder"
        );

        return $grade;
    }

    /**
     * `batch` yang dapat dibaca manusia dari keterangan detail.
     */
    protected function batchDariKeterangan(?string $keterangan): ?string
    {
        return TransaksiMasukDetail::batchDariKeterangan($keterangan);
    }

    /**
     * Data master untuk form (dipakai create & edit).
     *
     * @return array<string, mixed>
     */
    protected function dataMasterForm(string $kodeKategori, bool $butuhSatuan = false): array
    {
        $barang = Barang::query()
            ->aktif()
            ->with(['kategori', 'satuanDasar'])
            ->whereHas('kategori', fn ($q) => $q->where('kode', $kodeKategori))
            ->orderBy('kode_barang')
            ->get();

        // Grade hanya untuk kategori terkait (grade lintas kategori tetap
        // ditolak validasi bila dikirim lewat request yang dimanipulasi).
        $kategoriId = $barang->first()?->kategori_id;

        $grade = Grade::query()
            ->aktif()
            ->when($kategoriId !== null, fn ($q) => $q->where('kategori_id', $kategoriId))
            ->orderBy('kode')
            ->get();

        return [
            'kodeKategori' => $kodeKategori,
            'daftarBarang' => $barang,
            'daftarGrade' => $grade,
            'daftarSatuan' => $butuhSatuan ? Satuan::orderBy('kode')->get() : collect(),
            'daftarPartner' => SupplierCustomer::query()->orderBy('kode')->get(),
            'daftarShift' => (array) config('wms.shift', []),
            'labelShift' => $this->labelShift(),
        ];
    }

    /**
     * Label ringkas shift untuk dropdown, e.g. "1 (06:00–14:00)".
     *
     * @return array<int, string>
     */
    protected function labelShift(): array
    {
        $label = [];

        foreach ((array) config('wms.shift', []) as $nomor => $jam) {
            $label[(int) $nomor] = sprintf('%d (%s–%s)', $nomor, $jam['mulai'], $jam['selesai']);
        }

        return $label;
    }

    /**
     * Payload data JSON untuk JavaScript baris dinamis.
     *
     * @return array<string, mixed>
     */
    protected function payloadJs(string $kodeKategori, bool $butuhSatuan = false): array
    {
        $master = $this->dataMasterForm($kodeKategori, $butuhSatuan);

        return [
            'barang' => $master['daftarBarang']->map(fn (Barang $b) => [
                'id' => $b->id,
                'kode' => $b->kode_barang,
                'nama' => $b->nama_barang,
                'satuanDasarId' => $b->satuan_dasar_id,
                'satuanDasar' => $b->satuanDasar?->kode,
            ])->values()->all(),
            'grade' => $master['daftarGrade']->map(fn (Grade $g) => [
                'id' => $g->id,
                'kode' => $g->kode,
            ])->values()->all(),
            'satuan' => $butuhSatuan
                ? $master['daftarSatuan']->map(fn (Satuan $s) => ['id' => $s->id, 'kode' => $s->kode])->values()->all()
                : [],
            // Konstanta konversi display dari config (SSOT, tanpa hardcode).
            'konversi' => [
                'boxBobbin' => (float) config('wms.satuan.dty_box_bobbin'),
                'boxNettoKg' => (float) config('wms.satuan.dty_box_netto_kg'),
                'boxGrossKg' => (float) config('wms.satuan.dty_box_gross_kg'),
                'toleransiGrade' => PenerimaanService::petaToleransiGrade(),
            ],
        ];
    }

    /**
     * Detail yang sudah tersimpan -> struktur array form (untuk old() fallback).
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function detailKeArray(TransaksiMasuk $transaksi): Collection
    {
        return $transaksi->detail()->with('satuanKemasan')->get()->map(
            fn (TransaksiMasukDetail $d) => [
                'barang_id' => $d->barang_id,
                'merge' => $d->merge,
                'grade_id' => $d->grade_id,
                'jumlah_kemasan' => $d->jumlah_kemasan,
                'satuan_kemasan_id' => $d->satuan_kemasan_id,
                'jumlah_bobbin' => $d->jumlah_bobbin,
                'jumlah_box' => $this->jumlahBoxDetail($d),
                'berat_kg' => (float) $d->berat_kg,
                'berat_gross_kg' => TransaksiMasukDetail::grossDariKeterangan($d->keterangan),
                // `batch` untuk item pendukung; penanda GROSS dibuang dari input.
                'keterangan' => TransaksiMasukDetail::batchDariKeterangan($d->keterangan) ?? '',
            ]
        )->values();
    }

    /**
     * Jumlah box dari satu detail (untuk form DTY/POY).
     */
    protected function jumlahBoxDetail(TransaksiMasukDetail $detail): ?int
    {
        if ($detail->satuanKemasan?->kode === PenerimaanService::SATUAN_BOX && $detail->jumlah_kemasan !== null) {
            return (int) round((float) $detail->jumlah_kemasan);
        }

        return null;
    }

    /**
     * Header transaksi dari input tervalidasi (field yang sama untuk semua jenis).
     *
     * `sumber_internal` & `merge_poy_asal` hanya relevan untuk DTY_PRODUKSI
     * tetapi aman dibawa sebagai null untuk jenis lain.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerDari(array $data): array
    {
        return [
            'tanggal' => $data['tanggal'],
            'partner_id' => $data['partner_id'] ?? null,
            'ref_dokumen' => $data['ref_dokumen'] ?? null,
            'shift' => $data['shift'] ?? null,
            'operator_nama' => $data['operator_nama'] ?? null,
            'pengirim' => $data['pengirim'] ?? null,
            'penerima' => $data['penerima'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ];
    }

    /**
     * Cek status dokumen sebelum aksi posting/issue (pesan ramah pengguna).
     *
     * Policy sudah menolak 403 untuk non-DRAFT; pengecekan ini memberi
     * pesan jelas bila status berubah di antara render halaman & submit
     * (mis. dua tab), dan menjadi lapis kedua anti double-posting.
     */
    protected function pesanBukanDraft(TransaksiMasuk $transaksi): ?string
    {
        if ($transaksi->status === StatusDokumen::Draft) {
            return null;
        }

        return sprintf(
            'Transaksi %s berstatus %s dan tidak dapat diproses ulang.',
            $transaksi->nomor_transaksi,
            $transaksi->status->value,
        );
    }

    /**
     * Nomor transaksi baru (atomik) untuk transaksi masuk.
     */
    protected function nomorTransaksiBaru(NomorDokumenService $nomor): string
    {
        return $nomor->generate('TRX_MASUK');
    }

    /**
     * Filter index (tanggal dari–sampai, status, pencarian nomor) sebagai query
     * string agar `withQueryString()` di pagination ikut terbawa.
     *
     * @return array<string, mixed>
     */
    protected function filterIndex(Request $request, string $jenis): array
    {
        return [
            'dari' => $request->query('dari'),
            'sampai' => $request->query('sampai'),
            'status' => $request->query('status'),
            'cari' => trim((string) $request->query('cari', '')),
            'jenis' => $jenis,
        ];
    }
}
