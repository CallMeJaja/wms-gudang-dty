<?php

namespace App\Http\Requests\Inbound;

use App\Enums\JenisMasuk;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Services\PenerimaanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Basis validasi form penerimaan (F2B).
 *
 * Menangani bagian yang SAMA untuk ketiga jenis penerimaan:
 *  - header (tanggal, partner, pengirim, penerima, catatan, shift)
 *  - susunan array detail.* (minimal 1 baris, dinamis)
 *  - validasi lintas-field yang butuh akses DB:
 *      * barang detail harus se-kategori dengan jenis transaksi
 *      * grade harus milik kategori barang (PRD §7.5)
 *      * merge wajib & berat kg > 0
 *      * DTY: peringatan gross per box di luar toleransi grade (§3.3)
 *
 * Otorisasi tetap di Policy (controller), sesuai konvensi F1.
 */
abstract class PenerimaanRequest extends FormRequest
{
    abstract protected function jenis(): JenisMasuk;

    public function authorize(): bool
    {
        // Otorisasi ditangani Gate::authorize() di controller (PenerimaanPolicy).
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalisasi input sebelum validasi
    |--------------------------------------------------------------------------
    */

    protected function prepareForValidation(): void
    {
        $detail = $this->input('detail');

        if (is_array($detail)) {
            $detail = $this->rapikanDetail($detail);
        }

        $this->merge([
            'detail' => $detail,
            // Angka kosong dari form ("" atau "-") dijadikan null agar rule
            // nullable bekerja dan tidak dianggap 0.
            'shift' => $this->angkaAtauNull($this->input('shift')),
        ]);
    }

    /**
     * Buang baris kosong & normalisasi angka desimal gaya Indonesia.
     *
     * @param  array<int, array<string, mixed>>  $detail
     * @return array<int, array<string, mixed>>
     */
    protected function rapikanDetail(array $detail): array
    {
        $bersih = [];

        foreach (array_values($detail) as $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $barangId = $baris['barang_id'] ?? null;

            // Baris tanpa barang dianggap baris kosong (tombol tambah baris
            // yang tidak diisi) — dibuang agar tidak memicu error wajib isi.
            if ($barangId === null || $barangId === '' || (string) $barangId === '0') {
                $punyaIsi = trim((string) ($baris['merge'] ?? '')) !== ''
                    || trim((string) ($baris['berat_kg'] ?? '')) !== '';

                if (! $punyaIsi) {
                    continue;
                }
            }

            foreach (['jumlah_kemasan', 'jumlah_bobbin', 'berat_kg', 'berat_gross_kg'] as $kolom) {
                if (array_key_exists($kolom, $baris)) {
                    $baris[$kolom] = $this->desimalAtauNull($baris[$kolom]);
                }
            }

            $bersih[] = $baris;
        }

        return $bersih;
    }

    /**
     * Konversi "1.234,56" / "1,5" -> 1234.56 / 1.5. Null bila bukan angka.
     */
    protected function desimalAtauNull(mixed $nilai): ?float
    {
        if ($nilai === null) {
            return null;
        }

        if (is_int($nilai) || is_float($nilai)) {
            return (float) $nilai;
        }

        $teks = trim((string) $nilai);

        if ($teks === '') {
            return null;
        }

        // Gaya Indonesia: titik = ribuan, koma = desimal.
        if (str_contains($teks, ',')) {
            $teks = str_replace('.', '', $teks);
            $teks = str_replace(',', '.', $teks);
        }

        return is_numeric($teks) ? (float) $teks : null;
    }

    protected function angkaAtauNull(mixed $nilai): mixed
    {
        return $nilai === '' || $nilai === null ? null : $nilai;
    }

    /*
    |--------------------------------------------------------------------------
    | Aturan validasi
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return array_merge($this->aturanHeader(), $this->aturanDetail());
    }

    /**
     * Aturan header umum (dapat ditimpa turunan).
     *
     * `nomor_transaksi` sengaja TIDAK divalidasi sebagai input: nomor selalu
     * digenerate server (NomorDokumenService) agar atomik & tidak bisa dipalsukan.
     */
    protected function aturanHeader(): array
    {
        $shift = array_keys((array) config('wms.shift', []));

        return [
            'tanggal' => ['required', 'date'],
            'partner_id' => ['nullable', 'integer', 'exists:supplier_customer,id'],
            'ref_dokumen' => ['nullable', 'string', 'max:100'],
            'shift' => ['nullable', 'integer', 'in:'.implode(',', $shift)],
            'operator_nama' => ['nullable', 'string', 'max:100'],
            'pengirim' => ['nullable', 'string', 'max:100'],
            'penerima' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Aturan detail bersama + field opsional per jenis (didefinisikan turunan).
     */
    protected function aturanDetail(): array
    {
        return array_merge([
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.barang_id' => ['required', 'integer', 'exists:barang,id'],
            'detail.*.merge' => ['required', 'string', 'max:50'],
            // Grade wajib untuk POY & DTY (barang berbasis grade); item pendukung
            // tidak memiliki grade, sehingga rule-nya diganti di request turunan.
            'detail.*.grade_id' => ['required', 'integer', 'exists:grade,id'],
            'detail.*.berat_kg' => ['required', 'numeric', 'gt:0'],
        ], $this->aturanDetailKhusus());
    }

    /**
     * Aturan tambahan khas jenis penerimaan.
     */
    protected function aturanDetailKhusus(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi lintas-field
    |--------------------------------------------------------------------------
    */

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $detail = $this->input('detail');

            if (! is_array($detail) || $detail === []) {
                return;
            }

            $kodeKategori = PenerimaanService::kodeKategori($this->jenis());
            $kategoriIdPerBarang = Barang::query()
                ->with('kategori:id,kode')
                ->whereIn('id', array_filter(array_map(
                    fn ($b) => is_array($b) ? ($b['barang_id'] ?? null) : null,
                    $detail
                )))
                ->get()
                ->keyBy('id');

            $kategoriGrade = Grade::query()
                ->whereIn('id', array_filter(array_map(
                    fn ($b) => is_array($b) ? ($b['grade_id'] ?? null) : null,
                    $detail
                )))
                ->pluck('kategori_id', 'id');

            foreach ($detail as $i => $baris) {
                if (! is_array($baris)) {
                    continue;
                }

                $barang = $kategoriIdPerBarang->get((int) ($baris['barang_id'] ?? 0));

                if ($barang === null) {
                    continue; // sudah ditangkap rule exists
                }

                // 1) Barang harus se-kategori dengan jenis transaksi.
                if ($barang->kategori?->kode !== $kodeKategori) {
                    $validator->errors()->add(
                        "detail.{$i}.barang_id",
                        "Barang {$barang->kode_barang} bukan kategori {$kodeKategori} "
                        ."dan tidak boleh dipakai pada penerimaan {$this->jenis()->label()}."
                    );
                }

                // 2) Grade harus milik kategori barang (PRD §7.5).
                //    Item pendukung tidak memakai grade -> dilewati; validasi
                //    grade untuk pendukung ada di request turunannya.
                if ($kodeKategori === KategoriBarang::PENDUKUNG) {
                    continue;
                }

                $kategoriGradeId = $kategoriGrade->get((int) ($baris['grade_id'] ?? 0));

                if ($kategoriGradeId !== null && (int) $kategoriGradeId !== (int) $barang->kategori_id) {
                    $validator->errors()->add(
                        "detail.{$i}.grade_id",
                        "Grade yang dipilih bukan milik kategori barang {$barang->kode_barang}."
                    );
                }

                // 3) Merge wajib (bukan hanya spasi).
                if (trim((string) ($baris['merge'] ?? '')) === '') {
                    $validator->errors()->add("detail.{$i}.merge", 'Merge wajib diisi untuk baris ini.');
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Pesan & label
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal penerimaan wajib diisi.',
            'tanggal.date' => 'Tanggal penerimaan tidak valid.',
            'partner_id.exists' => 'Partner yang dipilih tidak ditemukan.',
            'shift.in' => 'Shift harus salah satu dari: '.implode(', ', array_keys((array) config('wms.shift', []))).'.',

            'detail.required' => 'Minimal satu baris detail harus diisi.',
            'detail.array' => 'Detail penerimaan tidak valid.',
            'detail.min' => 'Minimal satu baris detail harus diisi.',
            'detail.*.barang_id.required' => 'Barang wajib dipilih pada setiap baris detail.',
            'detail.*.barang_id.exists' => 'Barang pada baris detail tidak ditemukan.',
            'detail.*.merge.required' => 'Merge wajib diisi pada setiap baris detail.',
            'detail.*.grade_id.required' => 'Grade wajib dipilih pada setiap baris detail.',
            'detail.*.grade_id.exists' => 'Grade pada baris detail tidak ditemukan.',
            'detail.*.berat_kg.required' => 'Berat Netto (Kg) wajib diisi pada setiap baris detail.',
            'detail.*.berat_kg.numeric' => 'Berat Netto (Kg) harus berupa angka.',
            'detail.*.berat_kg.gt' => 'Berat Netto (Kg) harus lebih besar dari 0.',
            'detail.*.jumlah_kemasan.numeric' => 'Jumlah kemasan harus berupa angka.',
            'detail.*.jumlah_kemasan.min' => 'Jumlah kemasan tidak boleh negatif.',
            'detail.*.jumlah_bobbin.integer' => 'Jumlah bobbin harus berupa bilangan bulat.',
            'detail.*.jumlah_bobbin.min' => 'Jumlah bobbin tidak boleh negatif.',
            'detail.*.satuan_kemasan_id.exists' => 'Satuan kemasan tidak valid.',
            'detail.*.berat_gross_kg.numeric' => 'Berat Gross (Kg) harus berupa angka.',
            'detail.*.berat_gross_kg.gte' => 'Berat Gross (Kg) tidak boleh lebih kecil dari Berat Netto (Kg).',
            'detail.*.keterangan.max' => 'Keterangan baris terlalu panjang (maksimal 255 karakter).',
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal' => 'tanggal',
            'partner_id' => 'partner',
            'ref_dokumen' => 'referensi dokumen',
            'shift' => 'shift',
            'operator_nama' => 'nama operator',
            'pengirim' => 'pengirim',
            'penerima' => 'penerima',
        ];
    }
}
