<?php

namespace App\Http\Controllers\Outbound\Concerns;

use App\Models\Barang;
use App\Models\Grade;
use App\Models\Satuan;
use App\Models\SupplierCustomer;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiKeluarDetail;
use App\Services\NomorDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Bagian bersama ketiga controller pengeluaran (F3).
 *
 * Cerminan MenulisDetailPenerimaan untuk sisi keluar.
 */
trait MenulisDetailPengeluaran
{
    /**
     * Simpan baris detail hasil validasi (hapus lalu tulis ulang; DRAFT-only).
     *
     * @param  array<int, array<string, mixed>>  $detail
     */
    protected function tulisDetail(TransaksiKeluar $transaksi, array $detail): void
    {
        $transaksi->detail()->delete();

        foreach ($detail as $baris) {
            $transaksi->detail()->create([
                'barang_id' => (int) $baris['barang_id'],
                'merge' => trim((string) $baris['merge']),
                'grade_id' => (int) $baris['grade_id'],
                'jumlah_kemasan' => isset($baris['jumlah_kemasan']) && $baris['jumlah_kemasan'] !== ''
                    ? round((float) $baris['jumlah_kemasan'], 2)
                    : null,
                'satuan_kemasan_id' => isset($baris['satuan_kemasan_id']) && $baris['satuan_kemasan_id'] !== ''
                    ? (int) $baris['satuan_kemasan_id']
                    : null,
                'jumlah_bobbin' => isset($baris['jumlah_bobbin']) && $baris['jumlah_bobbin'] !== ''
                    ? (int) $baris['jumlah_bobbin']
                    : null,
                'berat_netto_kg' => round((float) $baris['berat_netto_kg'], 2),
                'berat_gross_kg' => isset($baris['berat_gross_kg']) && $baris['berat_gross_kg'] !== ''
                    ? round((float) $baris['berat_gross_kg'], 2)
                    : null,
                'keterangan' => isset($baris['keterangan']) && trim((string) $baris['keterangan']) !== ''
                    ? mb_substr(trim((string) $baris['keterangan']), 0, 255)
                    : null,
            ]);
        }
    }

    protected function dataMasterForm(string $kodeKategori, bool $butuhSatuan = false): array
    {
        $barang = Barang::query()
            ->aktif()
            ->with(['kategori', 'satuanDasar'])
            ->whereHas('kategori', fn ($q) => $q->where('kode', $kodeKategori))
            ->orderBy('kode_barang')
            ->get();

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
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function detailKeArray(TransaksiKeluar $transaksi): Collection
    {
        return $transaksi->detail()->get()->map(
            fn (TransaksiKeluarDetail $d) => [
                'barang_id' => $d->barang_id,
                'merge' => $d->merge,
                'grade_id' => $d->grade_id,
                'jumlah_kemasan' => $d->jumlah_kemasan !== null ? (float) $d->jumlah_kemasan : null,
                'satuan_kemasan_id' => $d->satuan_kemasan_id,
                'jumlah_bobbin' => $d->jumlah_bobbin,
                'berat_netto_kg' => (float) $d->berat_netto_kg,
                'berat_gross_kg' => $d->berat_gross_kg !== null ? (float) $d->berat_gross_kg : null,
                'keterangan' => $d->keterangan ?? '',
            ]
        )->values();
    }

    protected function nomorTransaksiBaru(NomorDokumenService $nomor): string
    {
        return $nomor->generate('TRX_KELUAR');
    }

    /**
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
