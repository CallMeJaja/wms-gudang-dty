<?php

namespace App\Http\Requests\Master;

use App\Models\KategoriBarang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi master data Barang (PRD §3.1, A11).
 *
 * Aturan kunci: `is_merge_minggu` hanya boleh true bila kategorinya POY.
 * Buffer Minggu hanya untuk merge POY (A11) — merge DTY/pendukung
 * tidak boleh ditandai.
 */
class BarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi ditangani Policy di controller.
        return true;
    }

    /**
     * Normalisasi checkbox agar nilai "on" / kosong menjadi boolean nyata,
     * sehingga rule `boolean` dapat bekerja dengan benar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_merge_minggu' => $this->boolean('is_merge_minggu'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }

    public function rules(): array
    {
        $barangId = $this->route('barang')?->id;

        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori_barang,id'],
            'kode_barang' => [
                'required', 'string', 'max:50',
                Rule::unique('barang', 'kode_barang')->ignore($barangId),
            ],
            'nama_barang' => ['required', 'string', 'max:150'],
            'denier' => ['nullable', 'string', 'max:20'],
            'filament' => ['nullable', 'string', 'max:20'],
            'satuan_dasar_id' => ['required', 'integer', 'exists:satuan,id'],
            'stok_minimum' => ['nullable', 'numeric', 'min:0'],
            'is_merge_minggu' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Validasi lintas-field: merge Minggu hanya untuk kategori POY (A11).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('is_merge_minggu')) {
                return;
            }

            $kategoriId = $this->input('kategori_id');
            $kategori = $kategoriId ? KategoriBarang::find($kategoriId) : null;

            if ($kategori && $kategori->kode !== KategoriBarang::POY) {
                $validator->errors()->add(
                    'is_merge_minggu',
                    'Penandaan "Merge Minggu" hanya berlaku untuk barang ber-kategori POY. '
                    ."Barang kategori {$kategori->kode} tidak boleh disisihkan ke buffer Minggu (A11)."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori barang wajib dipilih.',
            'kategori_id.exists' => 'Kategori barang tidak valid.',
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.unique' => 'Kode barang sudah dipakai barang lain.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'satuan_dasar_id.required' => 'Satuan dasar wajib dipilih.',
            'satuan_dasar_id.exists' => 'Satuan dasar tidak valid.',
            'stok_minimum.numeric' => 'Stok minimum harus berupa angka.',
            'stok_minimum.min' => 'Stok minimum tidak boleh negatif.',
            'denier.max' => 'Denier terlalu panjang (maksimal 20 karakter).',
            'filament.max' => 'Filament terlalu panjang (maksimal 20 karakter).',
        ];
    }

    public function attributes(): array
    {
        return [
            'kategori_id' => 'kategori',
            'kode_barang' => 'kode barang',
            'nama_barang' => 'nama barang',
            'satuan_dasar_id' => 'satuan dasar',
            'stok_minimum' => 'stok minimum',
            'is_merge_minggu' => 'merge minggu',
        ];
    }
}
