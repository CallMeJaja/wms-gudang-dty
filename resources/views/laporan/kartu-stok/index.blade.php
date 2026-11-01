@extends('layouts.app')

@section('title', 'Kartu Stok')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-journal-text me-1"></i> Kartu Stok</h1>
            <p class="text-body-secondary small mb-0">Saldo awal + saldo berjalan per baris, beserta kolom pos.</p>
        </div>
        @if ($barang_id !== null && $merge !== '')
            <div class="btn-group btn-group-sm">
                <a href="{{ route('laporan.kartu-stok.excel', request()->query()) }}" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </a>
                <a href="{{ route('laporan.kartu-stok.pdf', request()->query()) }}" class="btn btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak
                </button>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.kartu-stok.index') }}" class="row g-2 mb-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label for="barang_id" class="form-label small mb-1">Barang</label>
                    <select id="barang_id" name="barang_id" class="form-select form-select-sm">
                        <option value="">— Pilih barang —</option>
                        @foreach ($daftarBarang as $barang)
                            <option value="{{ $barang->id }}" @selected((int) $barang_id === (int) $barang->id)>{{ $barang->kode_barang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="merge" class="form-label small mb-1">Merge</label>
                    <input type="text" id="merge" name="merge" class="form-control form-control-sm" value="{{ $merge }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="grade_id" class="form-label small mb-1">Grade</label>
                    <select id="grade_id" name="grade_id" class="form-select form-select-sm">
                        <option value="">— Semua —</option>
                        @foreach ($daftarGrade as $grade)
                            <option value="{{ $grade->id }}" @selected((int) $grade_id === (int) $grade->id)>{{ $grade->kode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <label for="pos" class="form-label small mb-1">Pos</label>
                    <select id="pos" name="pos" class="form-select form-select-sm">
                        <option value="">— Semua —</option>
                        <option value="AKTIF" @selected($pos === 'AKTIF')>Aktif</option>
                        <option value="BUFFER" @selected($pos === 'BUFFER')>Buffer</option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-1">
                    <label for="dari" class="form-label small mb-1">Dari</label>
                    <input type="date" id="dari" name="dari" class="form-control form-control-sm" value="{{ $dari }}">
                </div>
                <div class="col-sm-6 col-md-1">
                    <label for="sampai" class="form-label small mb-1">Sampai</label>
                    <input type="date" id="sampai" name="sampai" class="form-control form-control-sm" value="{{ $sampai }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Tampilkan</button>
                </div>
            </form>

            @if ($barang_id !== null && $merge !== '')
                <div class="alert alert-info py-2 small">
                    Saldo awal per {{ $dari }}: <strong>{{ number_format($saldoAwal, 2, ',', '.') }} kg</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Ref / Keterangan</th>
                                <th>Jenis</th>
                                <th>Pos</th>
                                <th class="wms-num">Masuk (Kg)</th>
                                <th class="wms-num">Keluar (Kg)</th>
                                <th class="wms-num">Saldo (Kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($baris as $mutasi)
                                <tr>
                                    <td class="wms-num">{{ $mutasi->tanggal?->format('d-m-Y') }}</td>
                                    <td class="font-monospace small">{{ $mutasi->ref_nomor }}</td>
                                    <td class="small">{{ $mutasi->jenis_transaksi->label() }}</td>
                                    <td><span class="badge text-bg-{{ $mutasi->pos->value === 'BUFFER' ? 'warning' : 'success' }}">{{ $mutasi->pos->value }}</span></td>
                                    <td class="wms-num">{{ number_format((float) $mutasi->masuk_qty, 2, ',', '.') }}</td>
                                    <td class="wms-num">{{ number_format((float) $mutasi->keluar_qty, 2, ',', '.') }}</td>
                                    <td class="wms-num fw-semibold">{{ number_format((float) $mutasi->getAttribute('saldo_berjalan'), 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-body-secondary py-4">Tidak ada mutasi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-body-secondary small mb-0">Pilih barang dan isi merge untuk menampilkan kartu stok.</p>
            @endif
        </div>
    </div>
@endsection
