@extends('layouts.app')

@section('title', 'Cetak — Surat Jalan ' . ($transaksi->nomor_surat_jalan ?? $transaksi->nomor_transaksi))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 wms-no-print">
        <a href="{{ route('outbound.surat-jalan.show', $transaksi) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke detail
        </a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>

    <div class="card wms-print-page">
        <div class="card-body">
            <div class="text-center mb-3">
                <h2 class="h5 mb-0 fw-bold">SURAT JALAN</h2>
                <div class="font-monospace fw-semibold">No. {{ $transaksi->nomor_surat_jalan ?? '— (DRAF — belum diterbitkan)' }}</div>
                <div class="small text-body-secondary">Tanggal: {{ $transaksi->tanggal?->format('d-m-Y') }}</div>
            </div>

            <div class="row g-3 mb-3 small">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th style="width: 9rem">Nama Langganan</th>
                            <td>: {{ $transaksi->partner?->nama ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>No. Kendaraan</th>
                            <td>: {{ $transaksi->no_kendaraan ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Sopir</th>
                            <td>: {{ $transaksi->nama_sopir ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th style="width: 9rem">Pengirim</th>
                            <td>: {{ $transaksi->pic_pengirim ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Mengetahui</th>
                            <td>: {{ $transaksi->pic_mengetahui ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 2.5rem">No</th>
                            <th>Item Code</th>
                            <th>Merge</th>
                            <th>Grade</th>
                            <th class="wms-num">Packing</th>
                            <th class="wms-num">Units (Bobbin)</th>
                            <th class="wms-num">Netto (Kg)</th>
                            <th class="wms-num">Gross (Kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi->detail as $i => $detail)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="font-monospace">{{ $detail->barang?->kode_barang }}</td>
                                <td class="font-monospace">{{ $detail->merge }}</td>
                                <td class="text-center">{{ $detail->grade?->kode }}</td>
                                <td class="wms-num">
                                    {{ $detail->jumlah_kemasan !== null ? number_format((float) $detail->jumlah_kemasan, 0, ',', '.') : '—' }}
                                    {{ $detail->satuanKemasan?->kode ? ' '.$detail->satuanKemasan->kode : '' }}
                                </td>
                                <td class="wms-num">{{ $detail->jumlah_bobbin !== null ? number_format((int) $detail->jumlah_bobbin, 0, ',', '.') : '—' }}</td>
                                <td class="wms-num">{{ number_format((float) $detail->berat_netto_kg, 2, ',', '.') }}</td>
                                <td class="wms-num">{{ $detail->berat_gross_kg !== null ? number_format((float) $detail->berat_gross_kg, 2, ',', '.') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">Total</td>
                            <td class="wms-num">{{ number_format((int) $transaksi->detail->sum('jumlah_bobbin'), 0, ',', '.') }}</td>
                            <td class="wms-num">{{ number_format($totalNetto, 2, ',', '.') }}</td>
                            <td class="wms-num">{{ number_format($totalGross, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($transaksi->catatan)
                <div class="small mt-2">
                    <strong>Catatan:</strong> {{ $transaksi->catatan }}
                </div>
            @endif

            <div class="row mt-4 small text-center">
                <div class="col-4">
                    <div class="mb-5">Penerima,</div>
                    <div>( ........................ )</div>
                </div>
                <div class="col-4">
                    <div class="mb-5">Pengirim,</div>
                    <div>( {{ $transaksi->pic_pengirim ?? '................' }} )</div>
                </div>
                <div class="col-4">
                    <div class="mb-5">Mengetahui,</div>
                    <div>( {{ $transaksi->pic_mengetahui ?? '................' }} )</div>
                </div>
            </div>
        </div>
    </div>
@endsection
