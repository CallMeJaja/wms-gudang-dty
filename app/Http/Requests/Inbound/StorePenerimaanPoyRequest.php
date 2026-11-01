<?php

namespace App\Http\Requests\Inbound;

use App\Enums\JenisMasuk;

/**
 * Validasi penerimaan POY dari IPCI (PRD §4.1).
 *
 * Kekhususan:
 *  - Merge WAJIB pada setiap baris (dipakai sebagai identitas stok A6).
 *  - Berat Netto (Kg) WAJIB — POY tidak boleh dikonversi dari bobbin (A1b).
 *  - Jumlah box & jumlah bobbin OPSIONAL (informatif saja).
 *  - Grade harus milik kategori POY (dicek lintas-field di parent).
 */
class StorePenerimaanPoyRequest extends PenerimaanRequest
{
    protected function jenis(): JenisMasuk
    {
        return JenisMasuk::PoyIpci;
    }

    protected function aturanHeader(): array
    {
        return array_merge(parent::aturanHeader(), [
            'shift' => ['required', 'integer', 'in:'.implode(',', array_keys((array) config('wms.shift', [])))],
            'operator_nama' => ['required', 'string', 'max:100'],
        ]);
    }

    protected function aturanDetailKhusus(): array
    {
        return [
            'detail.*.jumlah_box' => ['nullable', 'integer', 'min:0'],
            'detail.*.jumlah_bobbin' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'shift.required' => 'Shift wajib dipilih untuk penerimaan POY.',
            'operator_nama.required' => 'Nama operator wajib diisi (shift/akun dipakai bersama).',
            'detail.*.jumlah_box.integer' => 'Jumlah box harus berupa bilangan bulat.',
            'detail.*.jumlah_box.min' => 'Jumlah box tidak boleh negatif.',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'detail.*.jumlah_box' => 'jumlah box',
        ]);
    }
}
