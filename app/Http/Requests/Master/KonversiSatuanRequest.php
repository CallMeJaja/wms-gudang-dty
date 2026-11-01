<?php

namespace App\Http\Requests\Master;

use App\Models\Barang;
use App\Models\KonversiSatuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validasi master data Konversi Satuan (PRD §3.3, A1b).
 *
 * ATURAN KUNCI: konversi untuk barang kategori POY DITOLAK.
 * Saldo POY wajib berasal dari timbangan (Kg), bukan hasil kali
 * bobbin × faktor. POY tidak boleh dikonversi bobbin → kg (A1b).
 */
class KonversiSatuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // String kosong dari select "Global" → null.
        if ($this->input('barang_id') === '' || $this->input('barang_id') === '0') {
            $this->merge(['barang_id' => null]);
        }

        $this->merge([
            'is_default' => $this->has('is_default') ? $this->boolean('is_default') : false,
        ]);
    }

    public function rules(): array
    {
        $konversiId = $this->route('konversi')?->id;

        return [
            'barang_id' => ['nullable', 'integer', 'exists:barang,id'],
            'dari_satuan_id' => ['required', 'integer', 'exists:satuan,id'],
            'ke_satuan_id' => [
                'required', 'integer', 'exists:satuan,id', 'different:dari_satuan_id',
            ],
            'faktor' => ['required', 'numeric', 'gt:0'],
            'is_default' => ['boolean'],
        ];
    }

    /**
     * Validasi bisnis:
     * 1. Barang POY tidak boleh memiliki konversi (A1b).
     * 2. Kombinasi (barang, dari, ke) harus unik — termasuk bila barang_id NULL
     *    (unique index MySQL menganggap NULL berbeda, sehingga dicek manual).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $barangId = $this->input('barang_id');

            if ($barangId) {
                $barang = Barang::with('kategori')->find($barangId);

                if ($barang && $barang->isPoy()) {
                    $validator->errors()->add(
                        'barang_id',
                        'POY tidak boleh memiliki konversi satuan. Saldo POY wajib dihitung dari '
                        .'berat timbangan (Kg), bukan konversi bobbin/box (A1b).'
                    );
                }
            }

            $duplikat = $this->queryDuplikat();
            if ($duplikat) {
                $validator->errors()->add(
                    'ke_satuan_id',
                    'Kombinasi konversi (barang, satuan asal, satuan tujuan) sudah ada.'
                );
            }
        });
    }

    /**
     * Cari baris konversi yang identik selain baris sedang diedit.
     */
    private function queryDuplikat(): bool
    {
        $konversiId = $this->route('konversi')?->id;

        $query = KonversiSatuan::query()
            ->where('dari_satuan_id', $this->input('dari_satuan_id'))
            ->where('ke_satuan_id', $this->input('ke_satuan_id'));

        $barangId = $this->input('barang_id');
        if ($barangId) {
            $query->where('barang_id', $barangId);
        } else {
            $query->whereNull('barang_id');
        }

        if ($konversiId) {
            $query->whereKeyNot($konversiId);
        }

        return $query->exists();
    }

    public function messages(): array
    {
        return [
            'barang_id.exists' => 'Barang tidak valid.',
            'dari_satuan_id.required' => 'Satuan asal wajib dipilih.',
            'dari_satuan_id.exists' => 'Satuan asal tidak valid.',
            'ke_satuan_id.required' => 'Satuan tujuan wajib dipilih.',
            'ke_satuan_id.exists' => 'Satuan tujuan tidak valid.',
            'ke_satuan_id.different' => 'Satuan asal dan tujuan tidak boleh sama.',
            'faktor.required' => 'Faktor konversi wajib diisi.',
            'faktor.numeric' => 'Faktor konversi harus berupa angka.',
            'faktor.gt' => 'Faktor konversi harus lebih besar dari 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'barang_id' => 'barang',
            'dari_satuan_id' => 'satuan asal',
            'ke_satuan_id' => 'satuan tujuan',
            'faktor' => 'faktor',
            'is_default' => 'konversi default',
        ];
    }
}
