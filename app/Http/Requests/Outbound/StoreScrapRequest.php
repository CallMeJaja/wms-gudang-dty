<?php

namespace App\Http\Requests\Outbound;

use App\Enums\JenisKeluar;

class StoreScrapRequest extends PengeluaranRequest
{
    protected function jenis(): JenisKeluar
    {
        return JenisKeluar::DtyScrapReject;
    }

    protected function aturanHeader(): array
    {
        return array_merge(parent::aturanHeader(), [
            'catatan' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'catatan.required' => 'Catatan wajib diisi (tujuan/penerima scrap).',
            'catatan.min' => 'Catatan terlalu pendek — sebutkan tujuan/penerima scrap.',
        ]);
    }
}
