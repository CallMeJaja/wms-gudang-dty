<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Barang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;

/**
 * Konversi kemasan -> kilogram (PRD A1 / A1b).
 *
 * Aturan yang diimplementasikan:
 *  - KG tetap single source of truth. Konversi di sini HANYA membantu
 *    mengisi/validasi silang, saldo stok selalu berasal dari berat
 *    timbangan (Net Weight).
 *  - POY TIDAK BOLEH dikonversi bobbin -> kg. Bila operator hanya
 *    mengisi bobbin tanpa kg, service melempar pesan persis:
 *    "Berat Netto (Kg) wajib diisi untuk POY — konversi otomatis tidak tersedia."
 *  - DTY boleh di-auto-fill dari box (1 box = dty_box_bobbin bobbin
 *    = dty_box_netto_kg kg), tetap bisa dioverride manual oleh operator.
 *  - Pallet TIDAK dipakai sebagai konversi akuntansi (A2).
 */
class KonversiSatuanService
{
    /**
     * Hitung kg dari jumlah kemasan untuk satuan tertentu.
     *
     * Mengembalikan null bila tidak ada konversi tersedia (mis. POY yang
     * memang tidak memiliki konversi bobbin -> kg, atau satuan non-kemasan).
     *
     * @param  int  $satuanId  satuan kemasan asal (BOX / BOBBIN / PALLET ...).
     */
    public function hitungKgDariKemasan(Barang $barang, float $jumlahKemasan, int $satuanId): ?float
    {
        if ($jumlahKemasan <= 0) {
            return null;
        }

        // POY tidak punya konversi otomatis (A1b).
        if ($barang->isPoy()) {
            return null;
        }

        $satuanKgId = Satuan::query()->where('kode', Satuan::KG)->value('id');

        if ($satuanKgId === null) {
            return null;
        }

        // Satuan sumber sudah KG -> tidak perlu konversi.
        if ((int) $satuanId === (int) $satuanKgId) {
            return round($jumlahKemasan, 2);
        }

        $faktor = $this->cariFaktor($barang, $satuanId, (int) $satuanKgId);

        if ($faktor === null) {
            return null;
        }

        return round($jumlahKemasan * $faktor, 2);
    }

    /**
     * Validasi berat kg POY wajib diisi (A1b).
     *
     * Berat kg kosong/nol SELALU ditolak untuk POY — baik bobbin diisi
     * maupun tidak — karena POY tidak boleh dikonversi dari bobbin.
     *
     * @throws AturanBisnisException
     */
    public function validasiBeratPoy(?float $beratKg, ?int $jumlahBobbin): void
    {
        if ($beratKg === null || $beratKg <= 0) {
            throw AturanBisnisException::poyWajibKg();
        }
    }

    /**
     * Auto-fill DTY dari jumlah box (boleh dioverride manual oleh operator).
     *
     * Semua angka diambil dari config/wms.php (SSOT), tanpa hardcode.
     *
     * @return array{bobbin: int, netto_kg: float, gross_kg: float}
     */
    public function autoFillDty(float $jumlahBox): array
    {
        $bobbinPerBox = (float) config('wms.satuan.dty_box_bobbin');
        $nettoPerBox = (float) config('wms.satuan.dty_box_netto_kg');
        $grossPerBox = (float) config('wms.satuan.dty_box_gross_kg');

        return [
            'bobbin' => (int) round($jumlahBox * $bobbinPerBox),
            'netto_kg' => round($jumlahBox * $nettoPerBox, 2),
            'gross_kg' => round($jumlahBox * $grossPerBox, 2),
        ];
    }

    /**
     * Cari faktor konversi dari satuan asal ke KG.
     *
     * Mendukung rantai satu tingkat (mis. BOX -> BOBBIN -> KG) dengan
     * mengalikan faktor perantara, tanpa hardcode angka apa pun.
     */
    private function cariFaktor(Barang $barang, int $dariSatuanId, int $keSatuanId): ?float
    {
        $konversi = KonversiSatuan::query()
            ->where('barang_id', $barang->id)
            ->get(['dari_satuan_id', 'ke_satuan_id', 'faktor']);

        // Langsung.
        $langsung = $konversi->first(
            fn (KonversiSatuan $k) => (int) $k->dari_satuan_id === $dariSatuanId
                && (int) $k->ke_satuan_id === $keSatuanId
        );

        if ($langsung !== null) {
            return (float) $langsung->faktor;
        }

        // Satu perantara: dari -> X -> KG.
        foreach ($konversi->where('dari_satuan_id', $dariSatuanId) as $pertama) {
            $kedua = $konversi->first(
                fn (KonversiSatuan $k) => (int) $k->dari_satuan_id === (int) $pertama->ke_satuan_id
                    && (int) $k->ke_satuan_id === $keSatuanId
            );

            if ($kedua !== null) {
                return (float) $pertama->faktor * (float) $kedua->faktor;
            }
        }

        return null;
    }
}
