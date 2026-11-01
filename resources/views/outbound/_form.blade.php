{{--
    Partial bersama form pengeluaran (F3).

    Variabel yang diharapkan:
      $jenisForm   : 'poy' | 'surat-jalan' | 'scrap'
      $payloadJs   : array data master barang/grade/satuan (JSON)
      $detailAwal  : array baris detail awal (old() atau data tersimpan)
      $transaksi   : TransaksiKeluar|null
      $labelJudul  : judul kartu detail
--}}

@php
    $transaksi = $transaksi ?? null;
    $nilai = fn (string $kolom, $default = null) => old($kolom, $transaksi?->{$kolom} ?? $default);
    $detailLama = old('detail', $detailAwal);
@endphp

<script type="application/json" id="wms-outbound-data">@json($payloadJs)</script>

<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-card-heading me-1"></i> Data Dokumen
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if ($jenisForm === 'surat-jalan')
                        <div class="col-md-4">
                            <label for="nomor_surat_jalan" class="form-label">Nomor Surat Jalan</label>
                            <input type="text" id="nomor_surat_jalan" name="nomor_surat_jalan"
                                   class="form-control font-monospace @error('nomor_surat_jalan') is-invalid @enderror"
                                   value="{{ $nilai('nomor_surat_jalan', $transaksi?->nomor_surat_jalan ?? $nomorSjPratinjau ?? '') }}" maxlength="50">
                            @error('nomor_surat_jalan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Kosongkan untuk generate atomik saat Terbitkan; atau isi manual sesuai dokumen fisik.
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
                        <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal" name="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', $transaksi?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($jenisForm === 'surat-jalan')
                        <div class="col-md-4">
                            <label for="partner_id" class="form-label">Pelanggan (Langganan)</label>
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

                        <div class="col-md-3">
                            <label for="no_kendaraan" class="form-label">No Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" id="no_kendaraan" name="no_kendaraan"
                                   class="form-control @error('no_kendaraan') is-invalid @enderror"
                                   value="{{ $nilai('no_kendaraan') }}" maxlength="30" required>
                            @error('no_kendaraan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="nama_sopir" class="form-label">Nama Sopir <span class="text-danger">*</span></label>
                            <input type="text" id="nama_sopir" name="nama_sopir"
                                   class="form-control @error('nama_sopir') is-invalid @enderror"
                                   value="{{ $nilai('nama_sopir') }}" maxlength="100" required>
                            @error('nama_sopir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="pic_pengirim" class="form-label">PIC Pengirim <span class="text-danger">*</span></label>
                            <input type="text" id="pic_pengirim" name="pic_pengirim"
                                   class="form-control @error('pic_pengirim') is-invalid @enderror"
                                   value="{{ $nilai('pic_pengirim') }}" maxlength="100" required>
                            @error('pic_pengirim')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="pic_mengetahui" class="form-label">PIC Mengetahui <span class="text-danger">*</span></label>
                            <input type="text" id="pic_mengetahui" name="pic_mengetahui"
                                   class="form-control @error('pic_mengetahui') is-invalid @enderror"
                                   value="{{ $nilai('pic_mengetahui') }}" maxlength="100" required>
                            @error('pic_mengetahui')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($jenisForm === 'poy')
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

                    <div class="col-12">
                        <label for="catatan" class="form-label">
                            Catatan
                            @if ($jenisForm === 'scrap')
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <textarea id="catatan" name="catatan" rows="2"
                                  class="form-control @error('catatan') is-invalid @enderror"
                                  maxlength="2000"
                                  @if ($jenisForm === 'scrap') required placeholder="Wajib: tujuan/penerima scrap" @endif>{{ $nilai('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($jenisForm === 'poy')
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-start mb-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>
                    <strong>Hanya stok aktif yang boleh dipakai.</strong><br>
                    Stok cadangan (untuk hari Minggu) tidak dapat digunakan hari ini.
                    Pengeluaran melebihi stok aktif akan ditolak.
                </div>
            </div>
        </div>
    @elseif ($jenisForm === 'surat-jalan')
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-start mb-0" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Dokumen resmi Surat Jalan.</strong>
                    Stok baru berkurang setelah dokumen <strong>Diterbitkan (ISSUED)</strong>.
                    Beberapa Surat Jalan pada tanggal yang sama diizinkan.
                </div>
            </div>
        </div>
    @endif

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
                                <th style="width: 6rem">Kemasan</th>
                                <th style="width: 7rem">Bobbin</th>
                                <th style="width: 9rem">Netto (Kg) <span class="text-danger">*</span></th>
                                <th style="width: 9rem">Gross (Kg)</th>
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

                                    <td>
                                        <input type="number" min="0" step="0.01" name="detail[{{ $i }}][jumlah_kemasan]"
                                               class="form-control form-control-sm wms-num"
                                               value="{{ $nilaiBaris('jumlah_kemasan') }}" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="1" name="detail[{{ $i }}][jumlah_bobbin]"
                                               class="form-control form-control-sm wms-num"
                                               value="{{ $nilaiBaris('jumlah_bobbin') }}" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="0.01" name="detail[{{ $i }}][berat_netto_kg]"
                                               class="form-control form-control-sm wms-num @error("detail.{$i}.berat_netto_kg") is-invalid @enderror"
                                               value="{{ $nilaiBaris('berat_netto_kg') }}" required placeholder="0,00">
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="0.01" name="detail[{{ $i }}][berat_gross_kg]"
                                               class="form-control form-control-sm wms-num @error("detail.{$i}.berat_gross_kg") is-invalid @enderror"
                                               value="{{ $nilaiBaris('berat_gross_kg') }}" placeholder="0,00">
                                    </td>

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
            </div>
        </div>
    </div>
</div>

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
        <td>
            <select name="detail[0][grade_id]" class="form-select form-select-sm">
                <option value="">— Grade —</option>
                @foreach ($daftarGrade as $grade)
                    <option value="{{ $grade->id }}">{{ $grade->kode }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" min="0" step="0.01" name="detail[0][jumlah_kemasan]"
                   class="form-control form-control-sm wms-num" placeholder="0">
        </td>
        <td>
            <input type="number" min="0" step="1" name="detail[0][jumlah_bobbin]"
                   class="form-control form-control-sm wms-num" placeholder="0">
        </td>
        <td>
            <input type="number" min="0" step="0.01" name="detail[0][berat_netto_kg]"
                   class="form-control form-control-sm wms-num" required placeholder="0,00">
        </td>
        <td>
            <input type="number" min="0" step="0.01" name="detail[0][berat_gross_kg]"
                   class="form-control form-control-sm wms-num" placeholder="0,00">
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" data-wms-hapus-baris title="Hapus baris">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>
