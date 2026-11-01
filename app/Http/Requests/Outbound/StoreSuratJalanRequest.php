<?php

namespace App\Http\Requests\Outbound;

use App\Enums\JenisKeluar;
use Illuminate\Validation\Rule;

class StoreSuratJalanRequest extends PengeluaranRequest
{
    protected function jenis(): JenisKeluar
    {
        return JenisKeluar::DtySuratJalan;
    }

    protected function aturanHeader(): array
    {
        $transaksiId = $this->route('surat_jalan')?->id ?? $this->route('suratJalan')?->id;

        return array_merge(parent::aturanHeader(), [
            'nomor_surat_jalan' => [
                'nullable', 'string', 'max:50',
                Rule::unique('transaksi_keluar', 'nomor_surat_jalan')->ignore($transaksiId),
            ],
            'partner_id' => ['nullable', 'integer', 'exists:supplier_customer,id'],
            'no_kendaraan' => ['required', 'string', 'max:30'],
            'nama_sopir' => ['required', 'string', 'max:100'],
            'pic_pengirim' => ['required', 'string', 'max:100'],
            'pic_mengetahui' => ['required', 'string', 'max:100'],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'nomor_surat_jalan.unique' => 'Nomor Surat Jalan sudah dipakai transaksi lain.',
            'no_kendaraan.required' => 'Nomor kendaraan wajib diisi.',
            'nama_sopir.required' => 'Nama sopir wajib diisi.',
            'pic_pengirim.required' => 'PIC pengirim wajib diisi.',
            'pic_mengetahui.required' => 'PIC mengetahui wajib diisi.',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'nomor_surat_jalan' => 'nomor surat jalan',
            'no_kendaraan' => 'nomor kendaraan',
            'nama_sopir' => 'nama sopir',
            'pic_pengirim' => 'PIC pengirim',
            'pic_mengetahui' => 'PIC mengetahui',
        ]);
    }
}
