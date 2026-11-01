<?php

namespace App\Http\Requests\Outbound;

use App\Enums\JenisKeluar;
use App\Models\Barang;
use App\Models\Grade;
use App\Services\PengeluaranService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Basis validasi form pengeluaran (F3).
 *
 * Bagian yang SAMA untuk ketiga jenis keluar:
 *  - header (tanggal, catatan)
 *  - susunan array detail.* (minimal 1 baris, dinamis)
 *  - validasi lintas-field: barang se-kategori, grade se-kategori (PRD §7.5),
 *    merge wajib, netto > 0, gross >= netto.
 */
abstract class PengeluaranRequest extends FormRequest
{
    abstract protected function jenis(): JenisKeluar;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $detail = $this->input('detail');

        if (is_array($detail)) {
            $detail = $this->rapikanDetail($detail);
        }

        $this->merge([
            'detail' => $detail,
            'shift' => $this->angkaAtauNull($this->input('shift')),
        ]);
    }

    /**
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

            if ($barangId === null || $barangId === '' || (string) $barangId === '0') {
                $punyaIsi = trim((string) ($baris['merge'] ?? '')) !== ''
                    || trim((string) ($baris['berat_netto_kg'] ?? '')) !== '';

                if (! $punyaIsi) {
                    continue;
                }
            }

            foreach (['jumlah_kemasan', 'jumlah_bobbin', 'berat_netto_kg', 'berat_gross_kg'] as $kolom) {
                if (array_key_exists($kolom, $baris)) {
                    $baris[$kolom] = $this->desimalAtauNull($baris[$kolom]);
                }
            }

            $bersih[] = $baris;
        }

        return $bersih;
    }

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

    public function rules(): array
    {
        return array_merge($this->aturanHeader(), $this->aturanDetail());
    }

    protected function aturanHeader(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function aturanDetail(): array
    {
        return array_merge([
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.barang_id' => ['required', 'integer', 'exists:barang,id'],
            'detail.*.merge' => ['required', 'string', 'max:50'],
            'detail.*.grade_id' => ['required', 'integer', 'exists:grade,id'],
            'detail.*.berat_netto_kg' => ['required', 'numeric', 'gt:0'],
            'detail.*.berat_gross_kg' => ['nullable', 'numeric', 'gte:detail.*.berat_netto_kg'],
            'detail.*.jumlah_kemasan' => ['nullable', 'numeric', 'min:0'],
            'detail.*.satuan_kemasan_id' => ['nullable', 'integer', 'exists:satuan,id'],
            'detail.*.jumlah_bobbin' => ['nullable', 'integer', 'min:0'],
            'detail.*.keterangan' => ['nullable', 'string', 'max:255'],
        ], $this->aturanDetailKhusus());
    }

    protected function aturanDetailKhusus(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $detail = $this->input('detail');

            if (! is_array($detail) || $detail === []) {
                return;
            }

            $kodeKategori = PengeluaranService::kodeKategori($this->jenis());
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
                    continue;
                }

                if ($barang->kategori?->kode !== $kodeKategori) {
                    $validator->errors()->add(
                        "detail.{$i}.barang_id",
                        "Barang {$barang->kode_barang} bukan kategori {$kodeKategori} "
                        ."dan tidak boleh dipakai pada {$this->jenis()->label()}."
                    );
                }

                $kategoriGradeId = $kategoriGrade->get((int) ($baris['grade_id'] ?? 0));

                if ($kategoriGradeId !== null && (int) $kategoriGradeId !== (int) $barang->kategori_id) {
                    $validator->errors()->add(
                        "detail.{$i}.grade_id",
                        "Grade yang dipilih bukan milik kategori barang {$barang->kode_barang}."
                    );
                }

                if (trim((string) ($baris['merge'] ?? '')) === '') {
                    $validator->errors()->add("detail.{$i}.merge", 'Merge wajib diisi untuk baris ini.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal tidak valid.',

            'detail.required' => 'Minimal satu baris detail harus diisi.',
            'detail.array' => 'Detail tidak valid.',
            'detail.min' => 'Minimal satu baris detail harus diisi.',
            'detail.*.barang_id.required' => 'Barang wajib dipilih pada setiap baris detail.',
            'detail.*.barang_id.exists' => 'Barang pada baris detail tidak ditemukan.',
            'detail.*.merge.required' => 'Merge wajib diisi pada setiap baris detail.',
            'detail.*.grade_id.required' => 'Grade wajib dipilih pada setiap baris detail.',
            'detail.*.grade_id.exists' => 'Grade pada baris detail tidak ditemukan.',
            'detail.*.berat_netto_kg.required' => 'Berat Netto (Kg) wajib diisi pada setiap baris detail.',
            'detail.*.berat_netto_kg.numeric' => 'Berat Netto (Kg) harus berupa angka.',
            'detail.*.berat_netto_kg.gt' => 'Berat Netto (Kg) harus lebih besar dari 0.',
            'detail.*.berat_gross_kg.numeric' => 'Berat Gross (Kg) harus berupa angka.',
            'detail.*.berat_gross_kg.gte' => 'Berat Gross (Kg) tidak boleh lebih kecil dari Berat Netto (Kg).',
            'detail.*.jumlah_kemasan.numeric' => 'Jumlah kemasan harus berupa angka.',
            'detail.*.jumlah_kemasan.min' => 'Jumlah kemasan tidak boleh negatif.',
            'detail.*.jumlah_bobbin.integer' => 'Jumlah bobbin harus berupa bilangan bulat.',
            'detail.*.jumlah_bobbin.min' => 'Jumlah bobbin tidak boleh negatif.',
            'detail.*.satuan_kemasan_id.exists' => 'Satuan kemasan tidak valid.',
            'detail.*.keterangan.max' => 'Keterangan baris terlalu panjang (maksimal 255 karakter).',
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal' => 'tanggal',
            'shift' => 'shift',
            'operator_nama' => 'nama operator',
        ];
    }
}
