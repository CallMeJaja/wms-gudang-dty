{{--
    Partial halaman detail transaksi keluar (F3).

    Variabel: $transaksi, $jenisForm, $jenisKeluar, $totalNetto, $totalGross?,
              $judul, $labelAksi ('Posting' | 'Terbitkan'), $namaAksi,
              $cetakRoute? (nama route cetak, khusus Surat Jalan)
--}}

<div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2 wms-no-print">
    <div>
        <div class="small text-body-secondary mb-1">
            <a href="{{ route("outbound.{$jenisForm}.index") }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> {{ $judul }}
            </a>
        </div>
        <h1 class="h4 mb-1 font-monospace">{{ $transaksi->nomor_transaksi }}</h1>
        <div class="d-flex align-items-center gap-2">
            @include('outbound._badge-status', ['status' => $transaksi->status])
            <span class="text-body-secondary small">
                {{ $transaksi->jenis_keluar->label() }}
                @if ($transaksi->nomor_surat_jalan)
                    — SJ <span class="font-monospace">{{ $transaksi->nomor_surat_jalan }}</span>
                @endif
            </span>
        </div>
    </div>

    <div class="d-flex gap-2">
        @if (isset($cetakRoute))
            <a href="{{ route($cetakRoute, $transaksi) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-printer me-1"></i> Cetak
            </a>
        @endif

        @if ($transaksi->status === App\Enums\StatusDokumen::Draft)
            @can('update', [$transaksi, $jenisKeluar])
                <a href="{{ route("outbound.{$jenisForm}.edit", $transaksi) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Ubah Draf
                </a>
            @endcan

            @can('delete', [$transaksi, $jenisKeluar])
                <form method="POST" action="{{ route("outbound.{$jenisForm}.destroy", $transaksi) }}"
                      data-wms-confirm="Hapus draf {{ $transaksi->nomor_transaksi }}? Tindakan ini tidak dapat dibatalkan.">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">
                        <i class="bi bi-trash me-1"></i> Hapus Draf
                    </button>
                </form>
            @endcan

            @can($namaAksi, [$transaksi, $jenisKeluar])
                <form method="POST" action="{{ route("outbound.{$jenisForm}.{$namaAksi}", $transaksi) }}"
                      data-wms-confirm="{{ $labelAksi }} {{ $transaksi->nomor_transaksi }} sekarang? Stok akan berkurang dan tidak dapat dibatalkan tanpa void.">
                    @csrf
                    <button class="btn btn-success btn-sm" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> {{ $labelAksi }}
                    </button>
                </form>
            @endcan
        @elseif (in_array($transaksi->status, [App\Enums\StatusDokumen::Posted, App\Enums\StatusDokumen::Issued], true))
            @can('void', [$transaksi, $jenisKeluar])
                <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalVoid">
                    <i class="bi bi-x-circle me-1"></i> Batalkan (Void)
                </button>
            @endcan
        @endif
    </div>
</div>

@if (in_array($transaksi->status, [App\Enums\StatusDokumen::Posted, App\Enums\StatusDokumen::Issued], true))
    @can('void', [$transaksi, $jenisKeluar])
        <div class="modal fade wms-no-print" id="modalVoid" tabindex="-1" aria-labelledby="modalVoidLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route("outbound.{$jenisForm}.void", $transaksi) }}"
                      data-wms-confirm="Void {{ $transaksi->nomor_transaksi }}? Stok akan dikembalikan dan transaksi tidak dapat dipakai lagi.">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalVoidLabel">Batalkan (Void) {{ $transaksi->nomor_transaksi }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning small mb-3">
                                Stok dari transaksi ini akan dikembalikan ke posisi semula.
                            </div>
                            <label for="alasanVoid" class="form-label fw-semibold">Alasan void <span class="text-danger">*</span></label>
                            <textarea name="alasan_void" id="alasanVoid" rows="3" required minlength="10" maxlength="1000"
                                      class="form-control @error('alasan_void') is-invalid @enderror"
                                      placeholder="Minimal 10 karakter, mis. salah input berat timbangan...">{{ old('alasan_void') }}</textarea>
                            @error('alasan_void')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Ya, Void Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endif

@if ($transaksi->status === App\Enums\StatusDokumen::Void)
    <div class="alert alert-danger d-flex align-items-start" role="alert">
        <i class="bi bi-x-octagon me-2 mt-1"></i>
        <div>
            <div class="fw-semibold">Transaksi ini DIBATALKAN (void).</div>
            <div class="small">Alasan: {{ $transaksi->alasan_void ?? '—' }}</div>
            <div class="small text-body-secondary">
                {{ $transaksi->voided_at?->format('d-m-Y H:i') }} oleh {{ $transaksi->pemvoid?->name ?? '—' }}.
                Audit trail utuh — transaksi tidak dihapus.
            </div>
        </div>
    </div>
@endif

@if ($transaksi->status === App\Enums\StatusDokumen::Draft)
    <div class="alert alert-secondary d-flex align-items-center wms-no-print" role="alert">
        <i class="bi bi-hourglass-split me-2"></i>
        <div>
            Stok <strong>belum berubah</strong> pada status DRAF.
            Tekan <strong>{{ $labelAksi }}</strong> untuk mengurangi stok.
        </div>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-1"></i> Informasi Dokumen</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-body-secondary">Tanggal</dt>
                    <dd class="col-7">{{ $transaksi->tanggal?->format('d-m-Y') }}</dd>

                    @if ($transaksi->nomor_surat_jalan)
                        <dt class="col-5 text-body-secondary">No. Surat Jalan</dt>
                        <dd class="col-7 font-monospace">{{ $transaksi->nomor_surat_jalan }}</dd>
                    @endif

                    @if ($transaksi->partner)
                        <dt class="col-5 text-body-secondary">Partner</dt>
                        <dd class="col-7">{{ $transaksi->partner->nama }}</dd>
                    @endif

                    @if ($transaksi->no_kendaraan)
                        <dt class="col-5 text-body-secondary">No Kendaraan</dt>
                        <dd class="col-7">{{ $transaksi->no_kendaraan }}</dd>
                    @endif

                    @if ($transaksi->nama_sopir)
                        <dt class="col-5 text-body-secondary">Sopir</dt>
                        <dd class="col-7">{{ $transaksi->nama_sopir }}</dd>
                    @endif

                    @if ($transaksi->pic_pengirim)
                        <dt class="col-5 text-body-secondary">Pengirim</dt>
                        <dd class="col-7">{{ $transaksi->pic_pengirim }}</dd>
                    @endif

                    @if ($transaksi->pic_mengetahui)
                        <dt class="col-5 text-body-secondary">Mengetahui</dt>
                        <dd class="col-7">{{ $transaksi->pic_mengetahui }}</dd>
                    @endif

                    @if ($transaksi->shift)
                        <dt class="col-5 text-body-secondary">Shift</dt>
                        <dd class="col-7">{{ $transaksi->shift }}</dd>
                    @endif

                    @if ($transaksi->operator_nama)
                        <dt class="col-5 text-body-secondary">Operator</dt>
                        <dd class="col-7">{{ $transaksi->operator_nama }}</dd>
                    @endif

                    <dt class="col-5 text-body-secondary">Total Netto</dt>
                    <dd class="col-7 wms-num fw-semibold">{{ number_format($totalNetto, 2, ',', '.') }} kg</dd>

                    @if (isset($totalGross))
                        <dt class="col-5 text-body-secondary">Total Gross</dt>
                        <dd class="col-7 wms-num fw-semibold">{{ number_format($totalGross, 2, ',', '.') }} kg</dd>
                    @endif

                    <dt class="col-5 text-body-secondary">Dibuat</dt>
                    <dd class="col-7">{{ $transaksi->pembuat?->name ?? '—' }} — {{ $transaksi->created_at?->format('d-m-Y H:i') }}</dd>
                </dl>

                @if ($transaksi->catatan)
                    <hr>
                    <div class="small">
                        <div class="text-body-secondary">Catatan</div>
                        {{ $transaksi->catatan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-list-ol me-1"></i> Detail Barang</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Barang</th>
                                <th>Merge</th>
                                <th>Grade</th>
                                <th class="wms-num">Kemasan</th>
                                <th class="wms-num">Bobbin</th>
                                <th class="wms-num">Netto (Kg)</th>
                                <th class="wms-num">Gross (Kg)</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksi->detail as $i => $detail)
                                <tr>
                                    <td class="text-body-secondary">{{ $i + 1 }}</td>
                                    <td>
                                        <span class="font-monospace small">{{ $detail->barang?->kode_barang }}</span>
                                        <div class="small text-body-secondary">{{ $detail->barang?->nama_barang }}</div>
                                    </td>
                                    <td class="font-monospace small">{{ $detail->merge }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $detail->grade?->kode }}</span></td>
                                    <td class="wms-num">
                                        @if ($detail->jumlah_kemasan !== null)
                                            {{ number_format((float) $detail->jumlah_kemasan, 2, ',', '.') }}
                                            <span class="text-body-secondary">{{ $detail->satuanKemasan?->kode }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="wms-num">{{ $detail->jumlah_bobbin ?? '—' }}</td>
                                    <td class="wms-num fw-semibold">{{ number_format((float) $detail->berat_netto_kg, 2, ',', '.') }}</td>
                                    <td class="wms-num">{{ $detail->berat_gross_kg !== null ? number_format((float) $detail->berat_gross_kg, 2, ',', '.') : '—' }}</td>
                                    <td class="small">
                                        @if ($detail->keterangan)
                                            {{ $detail->keterangan }}
                                        @else
                                            <span class="text-body-tertiary">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="6" class="text-end">Total</th>
                                <th class="wms-num">{{ number_format($totalNetto, 2, ',', '.') }}</th>
                                <th class="wms-num">{{ isset($totalGross) ? number_format($totalGross, 2, ',', '.') : '' }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
