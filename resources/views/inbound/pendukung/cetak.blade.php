@extends('layouts.app')

@section('title', 'Cetak — Gate Pass ' . ($transaksi->nomor_gate_pass ?? $transaksi->nomor_transaksi))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 wms-no-print">
        <a href="{{ route('inbound.pendukung.show', $transaksi) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke detail
        </a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>

    <div class="card wms-print-page">
        <div class="card-body">
            <div class="mb-3 small">
                <div class="fw-semibold">To Security IPCI,</div>
                <div class="fw-semibold">Dept : STORE</div>
            </div>

            <div class="text-center mb-3">
                <h2 class="h5 mb-0 fw-bold">GATE PASS</h2>
                <div class="font-monospace fw-semibold">No. {{ $transaksi->nomor_gate_pass ?? '— (DRAF — belum diterbitkan)' }}</div>
                <div class="small text-body-secondary">Tanggal: {{ $transaksi->tanggal?->format('d-m-Y') }}</div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 2.5rem">NO</th>
                            <th>Material Code</th>
                            <th>Material Description</th>
                            <th>UoM</th>
                            <th class="wms-num">Qty</th>
                            <th>Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi->detail as $i => $detail)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="font-monospace">{{ $detail->barang?->kode_barang }}</td>
                                <td>{{ $detail->barang?->nama_barang }}</td>
                                <td class="text-center">{{ $detail->barang?->satuanDasar?->kode ?? $detail->satuanKemasan?->kode }}</td>
                                <td class="wms-num">{{ number_format((float) $detail->berat_kg, 2, ',', '.') }}</td>
                                <td class="font-monospace small">{{ $detail->batch ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transaksi->catatan)
                <div class="small mt-2">
                    <strong>REMARKS:</strong> {{ $transaksi->nomor_transaksi }} — {{ $transaksi->catatan }}
                </div>
            @else
                <div class="small mt-2">
                    <strong>REMARKS:</strong> {{ $transaksi->nomor_transaksi }}
                </div>
            @endif

            <div class="row mt-4 small text-center">
                <div class="col-4">
                    <div class="mb-5">Prepared,</div>
                    <div>( ........................ )</div>
                </div>
                <div class="col-4">
                    <div class="mb-5">Approved,</div>
                    <div>( ........................ )</div>
                </div>
                <div class="col-4">
                    <div class="mb-5">Received,</div>
                    <div>( ........................ )</div>
                </div>
            </div>
        </div>
    </div>
@endsection
