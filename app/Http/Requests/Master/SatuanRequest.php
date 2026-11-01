<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SatuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $satuanId = $this->route('satuan')?->id;

        return [
            'kode' => [
                'required', 'string', 'max:20',
                Rule::unique('satuan', 'kode')->ignore($satuanId),
            ],
            'nama' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode satuan wajib diisi.',
            'kode.unique' => 'Kode satuan sudah dipakai.',
            'kode.max' => 'Kode satuan maksimal 20 karakter.',
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.max' => 'Nama satuan maksimal 100 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'kode',
            'nama' => 'nama',
        ];
    }
}
