@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-clipboard-check me-1"></i> Stock Opname</h1>
            <p class="text-body-secondary small mb-0">Pencatatan stok fisik &amp; penyesuaian otomatis (selisih = fisik &minus; sistem).</p>
        </div>
        <a href="{{ route('opname.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Opname Baru
        </a>
    </div>

    <form method="GET" action="{{ route('opname.index') }}" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" class="form-control" aria-label="Dari tanggal">
        </div>
        <div class="col-auto">
            <input type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="form-control" aria-label="Sampai tanggal">
        </div>
        <div class="col-auto">
            <select name="barang_id" class="form-select" aria-label="Barang">
                <option value="">— Semua barang —</option>
                @foreach ($daftarBarang as $barang)
                    <option value="{{ $barang->id }}" @selected(($filter['barang_id'] ?? '') == $barang->id)>{{ $barang->kode_barang }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nomor</th>
                    <th>Tanggal</th>
                    <th>Barang</th>
                    <th>Merge</th>
                    <th>Grade</th>
                    <th class="text-end">Sistem</th>
                    <th class="text-end">Fisik</th>
                    <th class="text-end">Selisih</th>
                    <th>Penyesuaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $row)
                    <tr>
                        <td><a href="{{ route('opname.show', $row) }}">{{ $row->nomor_opname }}</a></td>
                        <td>{{ $row->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $row->barang->kode_barang }}</td>
                        <td>{{ $row->merge }}</td>
                        <td>{{ $row->grade->kode }}</td>
                        <td class="text-end">{{ number_format((float) $row->stok_sistem_qty, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $row->stok_fisik_qty, 2, ',', '.') }}</td>
                        <td class="text-end fw-semibold @if((float) $row->selisih_qty > 0) text-success @elseif((float) $row->selisih_qty < 0) text-danger @else text-secondary @endif">
                            {{ number_format((float) $row->selisih_qty, 2, ',', '.') }}
                        </td>
                        <td class="small">
                            @if ($row->transaksi_masuk_id)
                                <span class="badge text-bg-success">IN</span> {{ $row->transaksiMasuk->nomor_transaksi ?? '' }}
                            @elseif ($row->transaksi_keluar_id)
                                <span class="badge text-bg-danger">OUT</span> {{ $row->transaksiKeluar->nomor_transaksi ?? '' }}
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-body-secondary">Belum ada data opname.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $daftar->links() }}
@endsection
