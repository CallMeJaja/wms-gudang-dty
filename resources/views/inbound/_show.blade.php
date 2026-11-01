{{--
    Partial halaman detail/ringkasan transaksi penerimaan (F2B).

    Variabel: $transaksi, $jenisForm, $jenisMasuk, $totalKg, $judul, $labelAksi
              ('Posting' | 'Terbitkan'), $namaAksi ('posting' | 'issue'),
              $peringatanGross (opsional, array string)
--}}

<div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
    <div>
        <div class="small text-body-secondary mb-1">
            <a href="{{ route("inbound.{$jenisForm}.index") }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> {{ $judul }}
            </a>
        </div>
        <h1 class="h4 mb-1 font-monospace">{{ $transaksi->nomor_transaksi }}</h1>
        <div class="d-flex align-items-center gap-2">
            @include('inbound._badge-status', ['status' => $transaksi->status])
            <span class="text-body-secondary small">
                {{ $transaksi->jenis_masuk->label() }}
                @if ($transaksi->nomor_gate_pass)
                    — Gate Pass <span class="font-monospace">{{ $transaksi->nomor_gate_pass }}</span>
                @endif
            </span>
        </div>
    </div>

    <div class="d-flex gap-2">
        @if ($transaksi->status === App\Enums\StatusDokumen::Draft)
            @can('update', [$transaksi, $jenisMasuk])
                <a href="{{ route("inbound.{$jenisForm}.edit", $transaksi) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Ubah Draf
                </a>
            @endcan

            @can('delete', [$transaksi, $jenisMasuk])
                <form method="POST" action="{{ route("inbound.{$jenisForm}.destroy", $transaksi) }}"
                      data-wms-confirm="Hapus draf {{ $transaksi->nomor_transaksi }}? Tindakan ini tidak dapat dibatalkan.">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">
                        <i class="bi bi-trash me-1"></i> Hapus Draf
                    </button>
                </form>
            @endcan

            @can($namaAksi, [$transaksi, $jenisMasuk])
                <form method="POST" action="{{ route("inbound.{$jenisForm}.{$namaAksi}", $transaksi) }}"
                      data-wms-confirm="{{ $labelAksi }} {{ $transaksi->nomor_transaksi }} sekarang? Stok akan diubah dan tidak dapat dibatalkan tanpa void.">
                    @csrf
                    <button class="btn btn-success btn-sm" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> {{ $labelAksi }}
                    </button>
                </form>
            @endcan
        @endif
    </div>
</div>

@if ($transaksi->status === App\Enums\StatusDokumen::Draft)
    <div class="alert alert-secondary d-flex align-items-center" role="alert">
        <i class="bi bi-hourglass-split me-2"></i>
        <div>
            Stok <strong>belum berubah</strong> pada status DRAF.
            @if ($namaAksi === 'issue')
                Tekan <strong>Terbitkan</strong> agar dokumen resmi Gate Pass keluar dan stok bertambah.
            @else
                Tekan <strong>Posting</strong> untuk menambah stok
                @if ($jenisForm === 'poy')
                    (dan menjalankan alokasi buffer Minggu bila memenuhi syarat)
                @endif
                .
            @endif
        </div>
    </div>
@endif

@if (! empty($peringatanGross))
    <div class="alert alert-warning" role="alert">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Peringatan toleransi gross (informasi):</div>
        <ul class="mb-0 small">
            @foreach ($peringatanGross as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
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

                    @if ($transaksi->partner)
                        <dt class="col-5 text-body-secondary">Partner</dt>
                        <dd class="col-7">{{ $transaksi->partner->nama }}</dd>
                    @endif

                    @if ($transaksi->sumber_internal)
                        <dt class="col-5 text-body-secondary">Sumber</dt>
                        <dd class="col-7">{{ $transaksi->sumber_internal }}</dd>
                    @endif

                    @if ($transaksi->merge_poy_asal)
                        <dt class="col-5 text-body-secondary">Merge POY Asal</dt>
                        <dd class="col-7 font-monospace">{{ $transaksi->merge_poy_asal }}</dd>
                    @endif

                    @if ($transaksi->ref_dokumen)
                        <dt class="col-5 text-body-secondary">Ref. Dokumen</dt>
                        <dd class="col-7">{{ $transaksi->ref_dokumen }}</dd>
                    @endif

                    @if ($transaksi->shift)
                        <dt class="col-5 text-body-secondary">Shift</dt>
                        <dd class="col-7">{{ $transaksi->shift }}</dd>
                    @endif

                    @if ($transaksi->operator_nama)
                        <dt class="col-5 text-body-secondary">Operator</dt>
                        <dd class="col-7">{{ $transaksi->operator_nama }}</dd>
                    @endif

                    @if ($transaksi->pengirim)
                        <dt class="col-5 text-body-secondary">Pengirim</dt>
                        <dd class="col-7">{{ $transaksi->pengirim }}</dd>
                    @endif

                    @if ($transaksi->penerima)
                        <dt class="col-5 text-body-secondary">Penerima</dt>
                        <dd class="col-7">{{ $transaksi->penerima }}</dd>
                    @endif

                    <dt class="col-5 text-body-secondary">Total Berat</dt>
                    <dd class="col-7 wms-num fw-semibold">{{ number_format($totalKg, 2, ',', '.') }} kg</dd>

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
                                <th class="wms-num">Berat (Kg)</th>
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
                                    <td class="wms-num fw-semibold">{{ number_format((float) $detail->berat_kg, 2, ',', '.') }}</td>
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
                                <th class="wms-num">{{ number_format($totalKg, 2, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Jejak alokasi buffer (khusus POY) --}}
    @if ($jenisForm === 'poy' && $transaksi->alokasiBuffer->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-header fw-semibold"><i class="bi bi-shield-check me-1"></i> Jejak Alokasi Buffer Minggu</div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        @foreach ($transaksi->alokasiBuffer as $alokasi)
                            <li>
                                Merge <span class="font-monospace">{{ $alokasi->merge }}</span>:
                                @if ($alokasi->dilewati)
                                    <span class="badge text-bg-secondary">Dilewati</span>
                                @else
                                    <span class="badge text-bg-warning">{{ number_format((float) $alokasi->jumlah_kg, 2, ',', '.') }} kg ke buffer</span>
                                @endif
                                — {{ $alokasi->keterangan }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
