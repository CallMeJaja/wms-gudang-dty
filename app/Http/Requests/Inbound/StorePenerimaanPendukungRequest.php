<?php

namespace App\Http\Requests\Inbound;

use App\Enums\JenisMasuk;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use App\Services\KonversiSatuanService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi penerimaan item pendukung / Gate Pass (PRD §4.2).
 *
 * Kekhususan:
 *  - `nomor_gate_pass` hasil generate NomorDokumenService, TAPI tetap bisa
 *    diedit manual (nomor fisik di dokumen Gate Pass berasal dari IPCI),
 *    sehingga hanya unik & maks 50 karakter.
 *  - Qty + satuan kemasan wajib (satuan dasar bisa PCS/ROLL/KG).
 *  - `batch` disimpan ke kolom `keterangan` pada detail.
 *  - Item pendukung TIDAK memakai grade (Gate Pass hanya No/Material Code/
 *    Description/UoM/Qty/Batch). Kolom grade_id relasi tetap diisi grade
 *    netral milik kategori PENDUKUNG (dibuat/ditemukan controller) karena
 *    skema `transaksi_masuk_detail.grade_id` NOT NULL.
 *  - Lifecycle dokumen resmi: DRAFT -> ISSUED (bukan POSTED).
 */
class StorePenerimaanPendukungRequest extends PenerimaanRequest
{
    protected function jenis(): JenisMasuk
    {
        return JenisMasuk::PendukungGatepass;
    }

    protected function aturanHeader(): array
    {
        $transaksiId = $this->route('pendukung')?->id;

        return array_merge(parent::aturanHeader(), [
            // Nullable: bila kosong, controller meng-generate dari service.
            'nomor_gate_pass' => [
                'nullable', 'string', 'max:50',
                Rule::unique('transaksi_masuk', 'nomor_gate_pass')->ignore($transaksiId),
            ],
        ]);
    }

    /**
     * Pendukung tidak memakai grade: grade_id TIDAK diminta dari pengguna,
     * dan berat_kg dihitung server dari Qty x satuan kemasan.
     */
    protected function aturanDetail(): array
    {
        return array_merge(parent::aturanDetail(), $this->aturanDetailKhusus());
    }

    protected function aturanDetailKhusus(): array
    {
        return [
            // Grade tidak relevan -> tidak diwajibkan & boleh kosong.
            'detail.*.grade_id' => ['nullable'],
            // Berat kg diturunkan dari Qty + satuan kemasan (bukan input manual).
            'detail.*.berat_kg' => ['nullable', 'numeric', 'min:0'],
            'detail.*.jumlah_kemasan' => ['required', 'numeric', 'gt:0'],
            'detail.*.satuan_kemasan_id' => ['required', 'integer', 'exists:satuan,id'],
            // Batch diisi ke kolom `keterangan` detail.
            'detail.*.keterangan' => ['nullable', 'string', 'max:255'],
            'detail.*.merge' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Hitung berat_kg dari Qty + satuan (1:1 bila satuan == satuan dasar).
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $detail = $this->input('detail');

        if (! is_array($detail)) {
            return;
        }

        $barangMap = Barang::query()
            ->whereIn('id', array_filter(array_map(fn ($b) => is_array($b) ? ($b['barang_id'] ?? null) : null, $detail)))
            ->get(['id', 'satuan_dasar_id', 'kode_barang'])
            ->keyBy('id');

        $konversi = app(KonversiSatuanService::class);
        $satuanKgId = Satuan::query()->where('kode', Satuan::KG)->value('id');

        foreach ($detail as $i => $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $barang = $barangMap->get((int) ($baris['barang_id'] ?? 0));
            $qty = isset($baris['jumlah_kemasan']) && is_numeric($baris['jumlah_kemasan'])
                ? (float) $baris['jumlah_kemasan']
                : null;
            $satuanId = isset($baris['satuan_kemasan_id']) ? (int) $baris['satuan_kemasan_id'] : null;

            if ($barang === null || $qty === null || $satuanId === null) {
                continue;
            }

            // Satuan kemasan == satuan dasar -> qty 1:1 (PCS/ROLL/KG).
            if ($satuanId === (int) $barang->satuan_dasar_id) {
                $detail[$i]['berat_kg'] = $qty;

                continue;
            }

            // Satuan dasar KG & satuan kemasan lain -> konversi lewat service.
            $kg = (int) $barang->satuan_dasar_id === (int) $satuanKgId
                ? $konversi->hitungKgDariKemasan($barang, $qty, $satuanId)
                : null;

            $detail[$i]['berat_kg'] = $kg;
        }

        $this->merge(['detail' => $detail]);
    }

    /**
     * Berat kg wajib bisa dihitung; grade diisi otomatis controller.
     */
    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            $detail = $this->input('detail');

            if (! is_array($detail)) {
                return;
            }

            $kategoriPendukungId = KategoriBarang::query()
                ->where('kode', KategoriBarang::PENDUKUNG)
                ->value('id');

            foreach ($detail as $i => $baris) {
                if (! is_array($baris)) {
                    continue;
                }

                // Qty wajib dapat dikonversi menjadi qty satuan dasar.
                if (($baris['berat_kg'] ?? null) === null || (float) $baris['berat_kg'] <= 0) {
                    $validator->errors()->add(
                        "detail.{$i}.jumlah_kemasan",
                        'Qty tidak dapat dikonversi ke satuan dasar barang. '
                        .'Pilih satuan kemasan yang sesuai atau periksa master konversi satuan.'
                    );
                }

                // Grade (bila dikirim) harus milik kategori pendukung.
                $gradeId = (int) ($baris['grade_id'] ?? 0);

                if ($gradeId > 0 && $kategoriPendukungId !== null) {
                    $sesuai = Grade::query()
                        ->whereKey($gradeId)
                        ->where('kategori_id', $kategoriPendukungId)
                        ->exists();

                    if (! $sesuai) {
                        $validator->errors()->add(
                            "detail.{$i}.grade_id",
                            'Item pendukung tidak memakai grade kategori lain — biarkan grade kosong.'
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'nomor_gate_pass.unique' => 'Nomor Gate Pass sudah dipakai transaksi lain.',
            'detail.*.jumlah_kemasan.required' => 'Qty wajib diisi pada setiap baris item pendukung.',
            'detail.*.jumlah_kemasan.gt' => 'Qty harus lebih besar dari 0.',
            'detail.*.satuan_kemasan_id.required' => 'Satuan kemasan wajib dipilih pada setiap baris detail.',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'nomor_gate_pass' => 'nomor gate pass',
            'detail.*.keterangan' => 'batch',
        ]);
    }
}
