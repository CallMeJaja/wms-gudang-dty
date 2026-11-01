{{--
    Partial bersama form penerimaan (F2B).

    Variabel yang diharapkan:
      $jenisForm   : 'poy' | 'pendukung' | 'dty'
      $payloadJs   : array data master barang/grade/satuan/konversi (JSON)
      $detailAwal  : array baris detail awal (old() atau data tersimpan)
      $transaksi   : TransaksiMasuk|null
      $labelJudul  : judul kartu detail
--}}

@php
    // $transaksi boleh tidak dikirim (halaman create) — gunakan null sebagai default.
    $transaksi = $transaksi ?? null;
    $nilai = fn (string $kolom, $default = null) => old($kolom, $transaksi?->{$kolom} ?? $default);
    $detailLama = old('detail', $detailAwal);
@endphp

{{-- Data master JSON: satu sumber untuk JavaScript (baris dinamis & auto-fill). --}}
<script type="application/json" id="wms-inbound-data">@json($payloadJs)</script>

<div class="row g-3">
    {{-- ---------------------------- HEADER ---------------------------- --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-card-heading me-1"></i> Data Dokumen
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if ($jenisForm === 'pendukung')
                        <div class="col-md-4">
                            <label for="nomor_gate_pass" class="form-label">
                                Nomor Gate Pass <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nomor_gate_pass" name="nomor_gate_pass"
                                   class="form-control font-monospace @error('nomor_gate_pass') is-invalid @enderror"
                                   value="{{ $nilai('nomor_gate_pass', $nomorGatePass ?? '') }}" maxlength="50">
                            @error('nomor_gate_pass')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Digenerate otomatis dari penomoran atomik; dapat diedit sesuai nomor fisik Gate Pass IPCI.
                            </div>
                        </div>
                    @else
                        <div class="col-md-4">
                            <label class="form-label text-body-secondary">Nomor Transaksi</label>
                            <input type="text" class="form-control font-monospace" disabled
                                   value="{{ $transaksi?->nomor_transaksi ?? ($nomorPratinjau ?? '—') }}">
                            <div class="form-text">Digenerate otomatis oleh sistem saat disimpan.</div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <label for="tanggal" class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal" name="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', $transaksi?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @unless ($jenisForm === 'dty')
                        <div class="col-md-4">
                            <label for="partner_id" class="form-label">Partner</label>
                            <select id="partner_id" name="partner_id"
                                    class="form-select @error('partner_id') is-invalid @enderror">
                                <option value="">— Pilih partner —</option>
                                @foreach ($daftarPartner as $partner)
                                    <option value="{{ $partner->id }}"
                                        @selected((string) $nilai('partner_id') === (string) $partner->id)>
                                        {{ $partner->kode }} — {{ $partner->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('partner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endunless

                    <div class="col-md-4">
                        <label for="ref_dokumen" class="form-label">Referensi Dokumen (Surat Pengantar)</label>
                        <input type="text" id="ref_dokumen" name="ref_dokumen"
                               class="form-control @error('ref_dokumen') is-invalid @enderror"
                               value="{{ $nilai('ref_dokumen') }}" maxlength="100">
                        @error('ref_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($jenisForm !== 'pendukung')
                        <div class="col-md-2">
                            <label for="shift" class="form-label">
                                Shift <span class="text-danger">*</span>
                            </label>
                            <select id="shift" name="shift"
                                    class="form-select @error('shift') is-invalid @enderror"
                                    required>
                                <option value="">— Pilih —</option>
                                @foreach ($labelShift as $nomor => $label)
                                    <option value="{{ $nomor }}" @selected((string) $nilai('shift') === (string) $nomor)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shift')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="operator_nama" class="form-label">
                                Nama Operator <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="operator_nama" name="operator_nama"
                                   class="form-control @error('operator_nama') is-invalid @enderror"
                                   value="{{ $nilai('operator_nama') }}" maxlength="100"
                                   placeholder="Nama operator di lapangan (akun dipakai bersama)">
                            @error('operator_nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($jenisForm === 'dty')
                        <div class="col-md-4">
                            <label for="sumber_internal" class="form-label">Sumber Internal</label>
                            <input type="text" id="sumber_internal" name="sumber_internal"
                                   class="form-control @error('sumber_internal') is-invalid @enderror"
                                   value="{{ $nilai('sumber_internal', $sumberInternalDefault ?? '') }}" maxlength="100">
                            @error('sumber_internal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="merge_poy_asal" class="form-label">
                                Merge POY Asal <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="merge_poy_asal" name="merge_poy_asal"
                                   class="form-control font-monospace @error('merge_poy_asal') is-invalid @enderror"
                                   value="{{ $nilai('merge_poy_asal') }}" maxlength="50"
                                   placeholder="mis. 4P1065">
                            @error('merge_poy_asal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Wajib untuk laporan rendemen POY vs DTY (§6.3).</div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <label for="pengirim" class="form-label">Pengirim</label>
                        <input type="text" id="pengirim" name="pengirim"
                               class="form-control @error('pengirim') is-invalid @enderror"
                               value="{{ $nilai('pengirim') }}" maxlength="100">
                        @error('pengirim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="penerima" class="form-label">Penerima</label>
                        <input type="text" id="penerima" name="penerima"
                               class="form-control @error('penerima') is-invalid @enderror"
                               value="{{ $nilai('penerima') }}" maxlength="100">
                        @error('penerima')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="2"
                                  class="form-control @error('catatan') is-invalid @enderror"
                                  maxlength="2000">{{ $nilai('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------- PERINGATAN KONTEKS ------------------------- --}}
    @if ($jenisForm === 'poy')
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-start mb-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>
                    <strong>Berat Netto (Kg) wajib — konversi otomatis tidak tersedia.</strong><br>
                    POY tidak boleh dikonversi dari jumlah bobbin/box. Saldo stok POY dihitung dari berat timbangan
                    (kg) hasil surat pengantar IPCI. Kolom jumlah box &amp; bobbin bersifat informatif.
                </div>
            </div>
        </div>
    @elseif ($jenisForm === 'pendukung')
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-start mb-0" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Dokumen resmi Gate Pass.</strong>
                    Isi Qty sesuai UoM pada dokumen dan nomor <em>batch</em> bila ada (contoh:
                    <code>GRN-VELVET</code>, <code>I-407-LR-405</code>).
                    Stok baru bertambah setelah dokumen <strong>Diterbitkan (ISSUED)</strong>.
                </div>
            </div>
        </div>
    @elseif ($jenisForm === 'dty')
        <div class="col-12">
            <div class="alert alert-info mb-0" role="alert">
                <i class="bi bi-calculator me-2"></i>
                <strong>Auto-fill box → bobbin → kg.</strong>
                Saat Jumlah Box diisi, sistem menghitung bobbin, netto, dan gross dari
                <code>config('wms.satuan')</code>; nilai tetap bisa diubah manual.
                Gross per box divalidasi terhadap rentang grade — di luar rentang
                hanya menjadi <em>peringatan</em> (bobbin sisa/putus sah), grade C tidak divalidasi.
            </div>
        </div>
    @endif

    {{-- ---------------------------- DETAIL ---------------------------- --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-list-ol me-1"></i> {{ $labelJudul }}</span>
                <button type="button" class="btn btn-sm btn-outline-primary" data-wms-tambah-baris>
                    <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body">
                @error('detail')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 3rem">#</th>
                                <th>Barang</th>
                                <th style="width: 9rem">Merge</th>
                                <th style="width: 8rem">Grade</th>

                                @if ($jenisForm === 'poy')
                                    <th style="width: 6rem">Box</th>
                                    <th style="width: 7rem">Bobbin</th>
                                    <th style="width: 9rem">Netto (Kg) <span class="text-danger">*</span></th>
                                @elseif ($jenisForm === 'pendukung')
                                    <th style="width: 7rem">Qty <span class="text-danger">*</span></th>
                                    <th style="width: 7rem">Satuan <span class="text-danger">*</span></th>
                                    <th style="width: 8rem">Batch</th>
                                    <th style="width: 8rem">Total (Kg)</th>
                                @else
                                    <th style="width: 6rem">Box <span class="text-danger">*</span></th>
                                    <th style="width: 6rem">Bobbin</th>
                                    <th style="width: 8rem">Netto (Kg) <span class="text-danger">*</span></th>
                                    <th style="width: 8rem">Gross (Kg) <span class="text-danger">*</span></th>
                                @endif

                                <th style="width: 4rem" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody data-wms-detail-body>
                            @foreach ($detailLama as $i => $baris)
                                @php
                                    $nilaiBaris = fn (string $kolom, $default = null) => $baris[$kolom] ?? $default;
                                @endphp
                                <tr data-wms-baris>
                                    <td class="text-body-secondary" data-wms-nomor-baris>{{ $i + 1 }}</td>

                                    <td>
                                        <select name="detail[{{ $i }}][barang_id]"
                                                class="form-select form-select-sm @error("detail.{$i}.barang_id") is-invalid @enderror">
                                            <option value="">— Pilih barang —</option>
                                            @foreach ($daftarBarang as $item)
                                                <option value="{{ $item->id }}"
                                                    @selected((string) $nilaiBaris('barang_id') === (string) $item->id)>
                                                    {{ $item->kode_barang }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="detail[{{ $i }}][merge]"
                                               class="form-control form-control-sm font-monospace @error("detail.{$i}.merge") is-invalid @enderror"
                                               value="{{ $nilaiBaris('merge') }}" maxlength="50" required>
                                    </td>

                                    @if ($jenisForm !== 'pendukung')
                                        <td>
                                            <select name="detail[{{ $i }}][grade_id]"
                                                    class="form-select form-select-sm @error("detail.{$i}.grade_id") is-invalid @enderror">
                                                <option value="">— Grade —</option>
                                                @foreach ($daftarGrade as $grade)
                                                    <option value="{{ $grade->id }}"
                                                        @selected((string) $nilaiBaris('grade_id') === (string) $grade->id)>
                                                        {{ $grade->kode }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endif

                                    @if ($jenisForm === 'poy')
                                        <td>
                                            <input type="number" min="0" step="1" name="detail[{{ $i }}][jumlah_box]"
                                                   class="form-control form-control-sm wms-num"
                                                   value="{{ $nilaiBaris('jumlah_box') }}" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="1" name="detail[{{ $i }}][jumlah_bobbin]"
                                                   class="form-control form-control-sm wms-num"
                                                   value="{{ $nilaiBaris('jumlah_bobbin') }}" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="0.01" name="detail[{{ $i }}][berat_kg]"
                                                   class="form-control form-control-sm wms-num @error("detail.{$i}.berat_kg") is-invalid @enderror"
                                                   value="{{ $nilaiBaris('berat_kg') }}" required placeholder="0,00">
                                        </td>
                                    @elseif ($jenisForm === 'pendukung')
                                        <td>
                                            <input type="number" min="0" step="0.01" name="detail[{{ $i }}][jumlah_kemasan]"
                                                   class="form-control form-control-sm wms-num @error("detail.{$i}.jumlah_kemasan") is-invalid @enderror"
                                                   value="{{ $nilaiBaris('jumlah_kemasan') }}" required placeholder="0">
                                        </td>
                                        <td>
                                            <select name="detail[{{ $i }}][satuan_kemasan_id]"
                                                    class="form-select form-select-sm @error("detail.{$i}.satuan_kemasan_id") is-invalid @enderror">
                                                <option value="">— Satuan —</option>
                                                @foreach ($daftarSatuan as $satuan)
                                                    <option value="{{ $satuan->id }}"
                                                        @selected((string) $nilaiBaris('satuan_kemasan_id') === (string) $satuan->id)>
                                                        {{ $satuan->kode }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="detail[{{ $i }}][keterangan]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $nilaiBaris('keterangan') }}" maxlength="255" placeholder="batch">
                                        </td>
                                        <td class="text-body-secondary small">
                                            Qty dikonversi otomatis ke satuan dasar saat disimpan.
                                        </td>
                                    @else
                                        <td>
                                            <input type="number" min="0" step="1" name="detail[{{ $i }}][jumlah_box]"
                                                   class="form-control form-control-sm wms-num @error("detail.{$i}.jumlah_box") is-invalid @enderror"
                                                   value="{{ $nilaiBaris('jumlah_box') }}" required placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="1" name="detail[{{ $i }}][jumlah_bobbin]"
                                                   class="form-control form-control-sm wms-num"
                                                   value="{{ $nilaiBaris('jumlah_bobbin') }}" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="0.01" name="detail[{{ $i }}][berat_kg]"
                                                   class="form-control form-control-sm wms-num @error("detail.{$i}.berat_kg") is-invalid @enderror"
                                                   value="{{ $nilaiBaris('berat_kg') }}" required placeholder="0,00">
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="0.01" name="detail[{{ $i }}][berat_gross_kg]"
                                                   class="form-control form-control-sm wms-num @error("detail.{$i}.berat_gross_kg") is-invalid @enderror"
                                                   value="{{ $nilaiBaris('berat_gross_kg') }}" required placeholder="0,00">
                                            <div class="form-text" data-wms-gross-info></div>
                                        </td>
                                    @endif

                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-wms-hapus-baris
                                                title="Hapus baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($jenisForm === 'dty')
                    <div class="mt-3 small text-body-secondary">
                        <strong>Toleransi gross per box (config):</strong>
                        @foreach ($toleransiGrade as $kode => $rentang)
                            <span class="badge text-bg-light border font-monospace">
                                {{ $kode }}: {{ number_format($rentang['min'], 2, ',', '.') }}–{{ number_format($rentang['max'], 2, ',', '.') }} kg
                            </span>
                        @endforeach
                        <span class="badge text-bg-light border">C: tidak divalidasi</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Template satu baris detail untuk JavaScript (cloneNode). --}}
<template id="wms-baris-template">
    <tr data-wms-baris>
        <td class="text-body-secondary" data-wms-nomor-baris>1</td>
        <td>
            <select name="detail[0][barang_id]" class="form-select form-select-sm">
                <option value="">— Pilih barang —</option>
                @foreach ($daftarBarang as $item)
                    <option value="{{ $item->id }}">{{ $item->kode_barang }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="detail[0][merge]" class="form-control form-control-sm font-monospace"
                   maxlength="50" required>
        </td>
        @if ($jenisForm !== 'pendukung')
            <td>
                <select name="detail[0][grade_id]" class="form-select form-select-sm">
                    <option value="">— Grade —</option>
                    @foreach ($daftarGrade as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->kode }}</option>
                    @endforeach
                </select>
            </td>
        @endif

        @if ($jenisForm === 'poy')
            <td>
                <input type="number" min="0" step="1" name="detail[0][jumlah_box]"
                       class="form-control form-control-sm wms-num" placeholder="0">
            </td>
            <td>
                <input type="number" min="0" step="1" name="detail[0][jumlah_bobbin]"
                       class="form-control form-control-sm wms-num" placeholder="0">
            </td>
            <td>
                <input type="number" min="0" step="0.01" name="detail[0][berat_kg]"
                       class="form-control form-control-sm wms-num" required placeholder="0,00">
            </td>
        @elseif ($jenisForm === 'pendukung')
            <td>
                <input type="number" min="0" step="0.01" name="detail[0][jumlah_kemasan]"
                       class="form-control form-control-sm wms-num" required placeholder="0">
            </td>
            <td>
                <select name="detail[0][satuan_kemasan_id]" class="form-select form-select-sm">
                    <option value="">— Satuan —</option>
                    @foreach ($daftarSatuan as $satuan)
                        <option value="{{ $satuan->id }}">{{ $satuan->kode }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="detail[0][keterangan]" class="form-control form-control-sm"
                       maxlength="255" placeholder="batch">
            </td>
            <td class="text-body-secondary small">—</td>
        @else
            <td>
                <input type="number" min="0" step="1" name="detail[0][jumlah_box]"
                       class="form-control form-control-sm wms-num" required placeholder="0">
            </td>
            <td>
                <input type="number" min="0" step="1" name="detail[0][jumlah_bobbin]"
                       class="form-control form-control-sm wms-num" placeholder="0">
            </td>
            <td>
                <input type="number" min="0" step="0.01" name="detail[0][berat_kg]"
                       class="form-control form-control-sm wms-num" required placeholder="0,00">
            </td>
            <td>
                <input type="number" min="0" step="0.01" name="detail[0][berat_gross_kg]"
                       class="form-control form-control-sm wms-num" required placeholder="0,00">
                <div class="form-text" data-wms-gross-info></div>
            </td>
        @endif

        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" data-wms-hapus-baris title="Hapus baris">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>
