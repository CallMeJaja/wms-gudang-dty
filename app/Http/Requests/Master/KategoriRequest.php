<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kategoriId = $this->route('kategori')?->id;

        return [
            'kode' => [
                'required', 'string', 'max:20',
                Rule::unique('kategori_barang', 'kode')->ignore($kategoriId),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode kategori wajib diisi.',
            'kode.unique' => 'Kode kategori sudah dipakai.',
            'kode.max' => 'Kode kategori maksimal 20 karakter.',
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 100 karakter.',
            'keterangan.max' => 'Keterangan terlalu panjang (maksimal 255 karakter).',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'kode',
            'nama' => 'nama',
            'keterangan' => 'keterangan',
        ];
    }
}
