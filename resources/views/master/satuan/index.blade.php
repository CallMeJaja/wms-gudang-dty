@extends('layouts.app')

@section('title', 'Master Satuan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-secondary small mb-0">Data referensi kritis — tidak dapat dihapus bila sudah dipakai.</p>
        <div>
            @can('create', App\Models\Satuan::class)
                <a href="{{ route('master.satuan.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Satuan
                </a>
            @endcan
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            @foreach ($errors->all() as $pesan)
                <div>{{ $pesan }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('master.satuan.index') }}" class="row g-2 mb-3">
                <div class="col-sm-6 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" placeholder="Cari kode / nama…"
                               value="{{ $cari }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Cari</button>
                    @if ($cari !== '')
                        <a href="{{ route('master.satuan.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th class="wms-num">Dipakai Barang</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="font-monospace">{{ $item->kode }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="wms-num">{{ $item->barang_count }}</td>
                                <td class="text-end text-nowrap">
                                    @include('master._partials.actions', [
                                        'model' => $item,
                                        'routeBase' => 'master.satuan',
                                        'label' => 'satuan '.$item->kode,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-body-secondary py-4">Tidak ada satuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $daftar->links() }}</div>
        </div>
    </div>
@endsection
