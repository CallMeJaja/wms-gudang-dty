@extends('layouts.app')

@section('title', 'Master Barang')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-secondary small mb-0">Daftar barang POY, DTY, dan item pendukung.</p>
        <div>
            @can('create', App\Models\Barang::class)
                <a href="{{ route('master.barang.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                </a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('master.barang.index') }}" class="row g-2 mb-3">
                <div class="col-sm-6 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" placeholder="Cari kode / nama barang…"
                               value="{{ $cari }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Cari</button>
                    @if ($cari !== '')
                        <a href="{{ route('master.barang.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>D/F</th>
                            <th>Satuan</th>
                            <th class="wms-num">Stok Min</th>
                            <th class="text-center">Merge Minggu</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="font-monospace small">{{ $item->kode_barang }}</td>
                                <td>{{ $item->nama_barang }}</td>
                                <td>
                                    <span class="badge text-bg-secondary">{{ $item->kategori?->kode ?? '—' }}</span>
                                </td>
                                <td>{{ $item->denier_filament ?? '—' }}</td>
                                <td>{{ $item->satuanDasar?->kode ?? '—' }}</td>
                                <td class="wms-num">{{ $item->stok_minimum !== null ? number_format((float) $item->stok_minimum, 2, ',', '.') : '—' }}</td>
                                <td class="text-center">
                                    @if ($item->is_merge_minggu)
                                        <span class="badge text-bg-warning"><i class="bi bi-check2"></i> Ya</span>
                                    @else
                                        <span class="text-body-tertiary">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge text-bg-success">Aktif</span>
                                    @else
                                        <span class="badge text-bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('update', $item)
                                        <a href="{{ route('master.barang.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $item)
                                        <form method="POST" action="{{ route('master.barang.destroy', $item) }}" class="d-inline"
                                              onsubmit="return confirm('Hapus barang {{ $item->kode_barang }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-body-secondary py-4">Tidak ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $daftar->links() }}
            </div>
        </div>
    </div>
@endsection
