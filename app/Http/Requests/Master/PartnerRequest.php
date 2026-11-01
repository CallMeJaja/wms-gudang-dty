<?php

namespace App\Http\Requests\Master;

use App\Models\SupplierCustomer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $partnerId = $this->route('partner')?->id;

        return [
            'kode' => [
                'required', 'string', 'max:30',
                Rule::unique('supplier_customer', 'kode')->ignore($partnerId),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(SupplierCustomer::JENIS)],
            'kontak' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode partner wajib diisi.',
            'kode.unique' => 'Kode partner sudah dipakai.',
            'nama.required' => 'Nama partner wajib diisi.',
            'jenis.required' => 'Jenis partner wajib dipilih.',
            'jenis.in' => 'Jenis partner tidak valid.',
            'kontak.max' => 'Kontak terlalu panjang (maksimal 100 karakter).',
            'alamat.max' => 'Alamat terlalu panjang (maksimal 255 karakter).',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'kode',
            'nama' => 'nama',
            'jenis' => 'jenis',
            'kontak' => 'kontak',
            'alamat' => 'alamat',
        ];
    }
}
