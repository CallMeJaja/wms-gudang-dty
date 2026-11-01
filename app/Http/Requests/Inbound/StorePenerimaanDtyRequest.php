<?php

namespace App\Http\Requests\Inbound;

use App\Enums\JenisMasuk;
use App\Models\Grade;
use App\Services\PenerimaanService;
use Illuminate\Validation\Validator;

/**
 * Validasi penerimaan DTY hasil produksi (PRD §4.4).
 *
 * Kekhususan:
 *  - Shift wajib, operator_nama wajib (akun dipakai bersama — A16).
 *  - `sumber_internal` = 'AREA PRODUKSI' (diisi controller bila kosong).
 *  - `merge_poy_asal` wajib untuk laporan rendemen §6.3.
 *  - Detail: jumlah_box wajib (> 0), berat_kg (netto) wajib,
 *    berat_gross_kg >= netto.
 *  - Gross per box di luar toleransi grade HANYA memunculkan PERINGATAN
 *    (bukan error), karena bobbin sisa/putus sah. Grade tanpa entri di
 *    config (mis. C) dilewati.
 */
class StorePenerimaanDtyRequest extends PenerimaanRequest
{
    protected function jenis(): JenisMasuk
    {
        return JenisMasuk::DtyProduksi;
    }

    protected function aturanHeader(): array
    {
        return array_merge(parent::aturanHeader(), [
            'shift' => ['required', 'integer', 'in:'.implode(',', array_keys((array) config('wms.shift', [])))],
            'operator_nama' => ['required', 'string', 'max:100'],
            'sumber_internal' => ['nullable', 'string', 'max:100'],
            'merge_poy_asal' => ['required', 'string', 'max:50'],
        ]);
    }

    protected function aturanDetailKhusus(): array
    {
        return [
            'detail.*.jumlah_box' => ['required', 'integer', 'gt:0'],
            'detail.*.jumlah_bobbin' => ['nullable', 'integer', 'min:0'],
            'detail.*.berat_gross_kg' => ['required', 'numeric', 'gte:detail.*.berat_kg'],
        ];
    }

    /**
     * Tambahan: kumpulkan peringatan toleransi gross per box (tidak memblokir).
     */
    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            $detail = $this->input('detail');

            if (! is_array($detail) || $detail === [] || $validator->errors()->isNotEmpty()) {
                return;
            }

            $kodeGrade = Grade::query()
                ->whereIn('id', array_map(fn ($b) => (int) ($b['grade_id'] ?? 0), $detail))
                ->pluck('kode', 'id');

            $peringatan = [];

            foreach ($detail as $i => $baris) {
                $box = (float) ($baris['jumlah_box'] ?? 0);
                $gross = (float) ($baris['berat_gross_kg'] ?? 0);

                if ($box <= 0 || $gross <= 0) {
                    continue;
                }

                $kode = $kodeGrade->get((int) ($baris['grade_id'] ?? 0));
                $grossPerBox = PenerimaanService::grossPerBox($gross, $box);

                if ($grossPerBox !== null && PenerimaanService::grossDiLuarToleransi($grossPerBox, $kode)) {
                    $rentang = PenerimaanService::rentangGrossGrade($kode);

                    $peringatan[] = sprintf(
                        'Baris %d: gross per box %s kg di luar toleransi grade %s (%s–%s kg). '
                        .'Transaksi tetap dapat disimpan — pastikan bobbin sisa/putus sudah sesuai SOP.',
                        $i + 1,
                        number_format($grossPerBox, 2, ',', '.'),
                        (string) $kode,
                        number_format($rentang['min'], 2, ',', '.'),
                        number_format($rentang['max'], 2, ',', '.'),
                    );
                }
            }

            if ($peringatan !== []) {
                // Disimpan sebagai flash untuk ditampilkan di halaman tujuan.
                session()->flash('peringatan', $peringatan);
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'shift.required' => 'Shift wajib dipilih untuk penerimaan DTY.',
            'operator_nama.required' => 'Nama operator wajib diisi (shift/akun dipakai bersama).',
            'merge_poy_asal.required' => 'Merge POY asal wajib diisi untuk laporan rendemen.',
            'detail.*.jumlah_box.required' => 'Jumlah box wajib diisi pada setiap baris DTY.',
            'detail.*.jumlah_box.gt' => 'Jumlah box harus lebih besar dari 0.',
            'detail.*.berat_gross_kg.required' => 'Berat Gross (Kg) wajib diisi pada setiap baris DTY.',
            'detail.*.berat_gross_kg.gte' => 'Berat Gross (Kg) tidak boleh lebih kecil dari Berat Netto (Kg).',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'merge_poy_asal' => 'merge POY asal',
            'sumber_internal' => 'sumber internal',
            'detail.*.jumlah_box' => 'jumlah box',
            'detail.*.berat_gross_kg' => 'berat gross',
        ]);
    }
}
