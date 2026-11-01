<?php

namespace App\Http\Requests\Master;

use App\Models\Grade;
use App\Models\KategoriBarang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi master data Grade (A7: grade unik per kategori).
 *
 * PROTEKSI KHUSUS: grade kode 'C' pada kategori DTY adalah grade scrap
 * yang WAJIB tetap ada (konfirmasi PIC — scrap grade C pernah keluar).
 * Grade ini tidak boleh dinonaktifkan maupun dihapus lewat UI.
 */
class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }

    public function rules(): array
    {
        /** @var Grade|null $grade */
        $grade = $this->route('grade');

        return [
            'kode' => [
                'required', 'string', 'max:10',
                Rule::unique('grade', 'kode')
                    ->where('kategori_id', $this->input('kategori_id'))
                    ->ignore($grade?->id),
            ],
            'kategori_id' => ['required', 'integer', 'exists:kategori_barang,id'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Validasi bisnis:
     * 1. Grade kode 'C' pada kategori DTY tidak boleh dinonaktifkan.
     * 2. Identitas grade C DTY tidak boleh diubah (kode/kategori) sehingga
     *    proteksi tidak bisa dilubangi via edit.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Grade|null $grade */
            $grade = $this->route('grade');

            if (! $grade) {
                return;
            }

            $kategoriLama = $grade->kategori?->kode;
            $kategoriBaru = KategoriBarang::find($this->input('kategori_id'))?->kode;

            $adalahGradeCDty = $grade->kode === Grade::KODE_SCRAP
                && $kategoriLama === KategoriBarang::DTY;

            if (! $adalahGradeCDty) {
                return;
            }

            if (! $this->boolean('is_active')) {
                $validator->errors()->add(
                    'is_active',
                    'Grade C pada kategori DTY adalah grade scrap yang wajib aktif (konfirmasi PIC). '
                    .'Grade ini tidak dapat dinonaktifkan.'
                );
            }

            if ($this->input('kode') !== $grade->kode || $kategoriBaru !== $kategoriLama) {
                $validator->errors()->add(
                    'kode',
                    'Grade C kategori DTY tidak boleh diubah kode maupun kategorinya.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode grade wajib diisi.',
            'kode.unique' => 'Kode grade sudah ada pada kategori yang sama.',
            'kode.max' => 'Kode grade maksimal 10 karakter.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori tidak valid.',
            'keterangan.max' => 'Keterangan terlalu panjang (maksimal 255 karakter).',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'kode grade',
            'kategori_id' => 'kategori',
            'keterangan' => 'keterangan',
            'is_active' => 'status aktif',
        ];
    }
}
