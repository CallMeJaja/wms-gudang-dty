<?php

namespace App\Http\Requests\Outbound;

use App\Enums\JenisKeluar;

class StorePengeluaranPoyRequest extends PengeluaranRequest
{
    protected function jenis(): JenisKeluar
    {
        return JenisKeluar::PoyProduksi;
    }

    protected function aturanHeader(): array
    {
        return array_merge(parent::aturanHeader(), [
            'shift' => ['required', 'integer', 'in:'.implode(',', array_keys((array) config('wms.shift', [])))],
            'operator_nama' => ['required', 'string', 'max:100'],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'shift.required' => 'Shift wajib dipilih untuk pengeluaran POY.',
            'shift.in' => 'Shift harus 1, 2, atau 3.',
            'operator_nama.required' => 'Nama operator wajib diisi (shift/akun dipakai bersama).',
        ]);
    }
}
