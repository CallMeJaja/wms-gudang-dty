@extends('layouts.app')

@section('title', 'Laporan Keluar-Masuk')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-arrow-left-right me-1"></i> Laporan Keluar-Masuk</h1>
            <p class="text-body-secondary small mb-0">Sumber: mutasi stok (hanya transaksi yang sudah diposting/diterbitkan).</p>
        </div>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('laporan.keluar-masuk.excel', request()->query()) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
            <a href="{{ route('laporan.keluar-masuk.pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.keluar-masuk.index') }}" class="row g-2 mb-3 align-items-end">
                <div class="col-sm-6 col-md-2">
                    <label for="dari" class="form-label small mb-1">Dari</label>
                    <input type="date" id="dari" name="dari" class="form-control form-control-sm" value="{{ $filter['dari'] }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="sampai" class="form-label small mb-1">Sampai</label>
                    <input type="date" id="sampai" name="sampai" class="form-control form-control-sm" value="{{ $filter['sampai'] }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="kategori" class="form-label small mb-1">Kategori</label>
                    <select id="kategori" name="kategori" class="form-select form-select-sm">
                        <option value="">— Semua —</option>
                        @foreach ($daftarKategori as $kategori)
                            <option value="{{ $kategori->kode }}" @selected($filter['kategori'] === $kategori->kode)>{{ $kategori->kode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="arus" class="form-label small mb-1">Jenis Transaksi</label>
                    <select id="arus" name="arus" class="form-select form-select-sm">
                        <option value="">— In/Out —</option>
                        <option value="MASUK" @selected($filter['arus'] === 'MASUK')>Masuk</option>
                        <option value="KELUAR" @selected($filter['arus'] === 'KELUAR')>Keluar</option>
                        <option value="VOID" @selected($filter['arus'] === 'VOID')>Void</option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="merge" class="form-label small mb-1">Merge</label>
                    <input type="text" id="merge" name="merge" class="form-control form-control-sm" value="{{ $filter['merge'] }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Terapkan</button>
                    <a href="{{ route('laporan.keluar-masuk.index') }}" class="btn btn-sm btn-link">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Merge</th>
                            <th>Grade</th>
                            <th>Jenis</th>
                            <th>Pos</th>
                            <th>Ref Nomor</th>
                            <th class="wms-num">Masuk (Kg)</th>
                            <th class="wms-num">Keluar (Kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($baris as $mutasi)
                            <tr>
                                <td class="wms-num">{{ $mutasi->tanggal?->format('d-m-Y') }}</td>
                                <td class="font-monospace small">{{ $mutasi->barang?->kode_barang }}</td>
                                <td class="font-monospace small">{{ $mutasi->merge }}</td>
                                <td class="text-center">{{ $mutasi->grade?->kode }}</td>
                                <td class="small">{{ $mutasi->jenis_transaksi->label() }}</td>
                                <td><span class="badge text-bg-{{ $mutasi->pos->value === 'BUFFER' ? 'warning' : 'success' }}">{{ $mutasi->pos->value }}</span></td>
                                <td class="font-monospace small">{{ $mutasi->ref_nomor }}</td>
                                <td class="wms-num">{{ number_format((float) $mutasi->masuk_qty, 2, ',', '.') }}</td>
                                <td class="wms-num">{{ number_format((float) $mutasi->keluar_qty, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-body-secondary py-4">Belum ada mutasi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="7" class="text-end">Total</td>
                            <td class="wms-num">{{ number_format($total['masuk'], 2, ',', '.') }}</td>
                            <td class="wms-num">{{ number_format($total['keluar'], 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
