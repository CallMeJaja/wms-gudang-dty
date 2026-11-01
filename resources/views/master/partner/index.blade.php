@extends('layouts.app')

@section('title', 'Master Partner')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-people me-1"></i> Master Partner</h1>
            <p class="text-body-secondary small mb-0">Supplier, customer, internal, dan partner gabungan.</p>
        </div>
        @can('create', App\Models\SupplierCustomer::class)
            <a href="{{ route('master.partner.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Partner
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('master.partner.index') }}" class="row g-2 mb-3">
                <div class="col-sm-5 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" placeholder="Cari kode / nama…"
                               value="{{ $cari }}">
                    </div>
                </div>
                <div class="col-sm-4 col-md-2">
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">Semua jenis</option>
                        @foreach ($daftarJenis as $j)
                            <option value="{{ $j }}" @selected($jenis === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
                    @if ($cari !== '' || $jenis !== '')
                        <a href="{{ route('master.partner.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="font-monospace small">{{ $item->kode }}</td>
                                <td>{{ $item->nama }}</td>
                                <td><span class="badge text-bg-info">{{ $item->jenis }}</span></td>
                                <td>{{ $item->kontak ?? '—' }}</td>
                                <td class="small">{{ $item->alamat ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @include('master._partials.actions', [
                                        'model' => $item,
                                        'routeBase' => 'master.partner',
                                        'label' => 'partner '.$item->kode,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary py-4">Tidak ada data partner.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $daftar->links() }}</div>
        </div>
    </div>
@endsection
