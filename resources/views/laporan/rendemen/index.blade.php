@extends('layouts.app')

@section('title', 'Rendemen POY vs DTY')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-pie-chart me-1"></i> Rekonsiliasi POY vs DTY</h1>
            <p class="text-body-secondary small mb-0">Rendemen = (DTY masuk &divide; POY keluar) &times; 100.</p>
        </div>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('laporan.rendemen.excel', request()->query()) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
            <a href="{{ route('laporan.rendemen.pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.rendemen.index') }}" class="row g-2 mb-3 align-items-end">
                <div class="col-sm-6 col-md-2">
                    <label for="dari" class="form-label small mb-1">Dari</label>
                    <input type="date" id="dari" name="dari" class="form-control form-control-sm" value="{{ $dari }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="sampai" class="form-label small mb-1">Sampai</label>
                    <input type="date" id="sampai" name="sampai" class="form-control form-control-sm" value="{{ $sampai }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Terapkan</button>
                    <a href="{{ route('laporan.rendemen.index') }}" class="btn btn-sm btn-link">Reset</a>
                </div>
            </form>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="small text-body-secondary">Total POY Keluar</div>
                        <div class="h5 mb-0">{{ number_format($hasil['totalPoy'], 2, ',', '.') }} kg</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="small text-body-secondary">Total DTY Masuk</div>
                        <div class="h5 mb-0">{{ number_format($hasil['totalDty'], 2, ',', '.') }} kg</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="small text-body-secondary">Rendemen</div>
                        <div class="h5 mb-0">{{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, ',', '.').'%' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3">
                        <div class="small text-body-secondary">Waste</div>
                        <div class="h5 mb-0">{{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, ',', '.').'%' }}</div>
                    </div>
                </div>
            </div>

            <h2 class="h6">Per Merge POY Asal</h2>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Merge POY Asal</th>
                            <th class="wms-num">POY Keluar (Kg)</th>
                            <th class="wms-num">DTY Masuk (Kg)</th>
                            <th class="wms-num">Rendemen</th>
                            <th class="wms-num">Waste</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hasil['perMerge'] as $r)
                            <tr>
                                <td class="font-monospace">{{ $r['merge'] === '' ? '— (tanpa merge)' : $r['merge'] }}</td>
                                <td class="wms-num">{{ number_format($r['poy'], 2, ',', '.') }}</td>
                                <td class="wms-num">{{ number_format($r['dty'], 2, ',', '.') }}</td>
                                <td class="wms-num">{{ $r['rendemen'] === null ? '-' : number_format($r['rendemen'], 2, ',', '.').'%' }}</td>
                                <td class="wms-num">{{ $r['waste'] === null ? '-' : number_format($r['waste'], 2, ',', '.').'%' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-body-secondary py-4">Belum ada data pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td class="text-end">Total</td>
                            <td class="wms-num">{{ number_format($hasil['totalPoy'], 2, ',', '.') }}</td>
                            <td class="wms-num">{{ number_format($hasil['totalDty'], 2, ',', '.') }}</td>
                            <td class="wms-num">{{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, ',', '.').'%' }}</td>
                            <td class="wms-num">{{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, ',', '.').'%' }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-secondary small mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i> {{ $disclaimer }}
            </div>
        </div>
    </div>
@endsection
