<?php

namespace App\Filament\Support;

use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Models\TransaksiMasukDetail;
use App\Services\KonversiSatuanService;
use App\Services\PenerimaanService;
use Illuminate\Validation\ValidationException;

/**
 * Menjembatani state form Filament ke kolom `transaksi_masuk_detail`.
 *
 * Form memakai field yang ramah operator (`Box`, `Berat Gross`) sedangkan
 * skema hanya mengenal `jumlah_kemasan` + `satuan_kemasan_id` dan penanda
 * `[GROSS=..]` pada `keterangan`. Semua transformasi bisnis dikumpulkan di
 * sini agar dapat diuji tanpa Livewire.
 */
final class PenerimaanDetailMapper
{
    public static function satuanBoxId(): ?int
    {
        return Satuan::query()->where('kode', PenerimaanService::SATUAN_BOX)->value('id');
    }

    public static function gradePendukungId(): int
    {
        $kategoriId = KategoriBarang::query()
            ->where('kode', KategoriBarang::PENDUKUNG)
            ->value('id');

        $kode = (string) config('wms.grade_pendukung_default', 'STD');

        $gradeId = $kategoriId !== null
            ? Grade::query()
                ->where('kategori_id', $kategoriId)
                ->where('kode', $kode)
                ->value('id')
            : null;

        if ($gradeId === null) {
            throw ValidationException::withMessages([
                'data.detail' => "Grade netral '{$kode}' untuk kategori PENDUKUNG belum ada. Jalankan seeder grade.",
            ]);
        }

        return (int) $gradeId;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function poy(array $data): array
    {
        unset($data['berat_gross_kg']);

        $data['merge'] = trim((string) ($data['merge'] ?? ''));
        $data['satuan_kemasan_id'] = self::satuanBoxId();
        $data['jumlah_kemasan'] = self::numericOrNull($data['jumlah_kemasan'] ?? null);
        $data['jumlah_bobbin'] = self::integerOrNull($data['jumlah_bobbin'] ?? null);
        $data['berat_kg'] = round((float) ($data['berat_kg'] ?? 0), 2);
        $data['keterangan'] = self::teksAtauNull($data['keterangan'] ?? null);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function dty(array $data): array
    {
        $gross = self::numericOrNull($data['berat_gross_kg'] ?? null);
        $netto = round((float) ($data['berat_kg'] ?? 0), 2);

        if ($gross !== null && $gross > 0 && $gross < $netto) {
            throw ValidationException::withMessages([
                'data.detail' => 'Berat Gross (Kg) tidak boleh lebih kecil dari Berat Netto (Kg).',
            ]);
        }

        $keterangan = (string) (TransaksiMasukDetail::batchDariKeterangan($data['keterangan'] ?? null) ?? '');

        if ($gross !== null && $gross > 0) {
            $keterangan = trim($keterangan.' '.TransaksiMasukDetail::penandaGross($gross));
        }

        unset($data['berat_gross_kg']);

        $data['merge'] = trim((string) ($data['merge'] ?? ''));
        $data['satuan_kemasan_id'] = self::satuanBoxId();
        $data['jumlah_kemasan'] = self::numericOrNull($data['jumlah_kemasan'] ?? null);
        $data['jumlah_bobbin'] = self::integerOrNull($data['jumlah_bobbin'] ?? null);
        $data['berat_kg'] = $netto;
        $data['keterangan'] = self::teksAtauNull($keterangan);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function pendukung(array $data): array
    {
        $data['merge'] = trim((string) ($data['merge'] ?? ''));
        $data['merge'] = $data['merge'] === '' ? 'TANPA-BATCH' : $data['merge'];
        $data['grade_id'] = self::gradePendukungId();
        $data['jumlah_kemasan'] = self::numericOrNull($data['jumlah_kemasan'] ?? null);
        $data['jumlah_bobbin'] = null;
        $data['berat_kg'] = self::beratPendukung($data);
        $data['keterangan'] = self::teksAtauNull($data['keterangan'] ?? null);

        return $data;
    }

    /**
     * Berat KG item pendukung dari Qty x satuan kemasan.
     *
     * @param  array<string, mixed>  $data
     */
    public static function beratPendukung(array $data): float
    {
        $barang = Barang::query()->with('kategori')->find($data['barang_id'] ?? null);

        if (! $barang instanceof Barang) {
            throw ValidationException::withMessages([
                'data.detail' => 'Barang pada baris detail tidak ditemukan.',
            ]);
        }

        $qty = self::numericOrNull($data['jumlah_kemasan'] ?? null);
        $satuanId = isset($data['satuan_kemasan_id']) ? (int) $data['satuan_kemasan_id'] : null;

        if ($qty === null || $qty <= 0 || $satuanId === null) {
            throw ValidationException::withMessages([
                'data.detail' => 'Qty dan satuan kemasan wajib diisi pada setiap baris item pendukung.',
            ]);
        }

        if ($satuanId === (int) $barang->satuan_dasar_id) {
            return round($qty, 2);
        }

        $kg = app(KonversiSatuanService::class)->hitungKgDariKemasan($barang, $qty, $satuanId);

        if ($kg === null) {
            throw ValidationException::withMessages([
                'data.detail' => "Qty {$barang->kode_barang} tidak dapat dikonversi ke KG. "
                    .'Pilih satuan yang sesuai atau lengkapi master konversi satuan.',
            ]);
        }

        return round($kg, 2);
    }

    public static function numericOrNull(mixed $nilai): ?float
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        return is_numeric($nilai) ? (float) $nilai : null;
    }

    public static function integerOrNull(mixed $nilai): ?int
    {
        $angka = self::numericOrNull($nilai);

        return $angka === null ? null : (int) round($angka);
    }

    private static function teksAtauNull(mixed $nilai): ?string
    {
        $teks = trim((string) $nilai);

        return $teks === '' ? null : mb_substr($teks, 0, 255);
    }
}
