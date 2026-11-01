@extends('layouts.app')

@section('title', 'Master Grade')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-award me-1"></i> Master Grade</h1>
            <p class="text-body-secondary small mb-0">Grade POY &amp; DTY. Grade C DTY (scrap) wajib dipertahankan.</p>
        </div>
        @can('create', App\Models\Grade::class)
            <a href="{{ route('master.grade.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Grade
            </a>
        @endcan
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
            <form method="GET" action="{{ route('master.grade.index') }}" class="row g-2 mb-3">
                <div class="col-sm-6 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" placeholder="Cari kode / keterangan…"
                               value="{{ $cari }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Cari</button>
                    @if ($cari !== '')
                        <a href="{{ route('master.grade.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Kode</th>
                            <th>Keterangan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $item)
                            @php
                                $terlindungi = $item->kode === $kodeScrap
                                    && $item->kategori?->kode === $kategoriDty;
                            @endphp
                            <tr>
                                <td><span class="badge text-bg-secondary">{{ $item->kategori?->kode ?? '—' }}</span></td>
                                <td class="font-monospace">
                                    {{ $item->kode }}
                                    @if ($terlindungi)
                                        <i class="bi bi-shield-lock text-danger ms-1"
                                           title="Grade scrap — tidak dapat dihapus/nonaktif"></i>
                                    @endif
                                </td>
                                <td class="small">{{ $item->keterangan ?? '—' }}</td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge text-bg-success">Aktif</span>
                                    @else
                                        <span class="badge text-bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('update', $item)
                                        <a href="{{ route('master.grade.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $item)
                                        @if ($terlindungi)
                                            <button class="btn btn-sm btn-outline-danger" type="button" disabled
                                                    title="Grade C DTY dilindungi">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('master.grade.destroy', $item) }}" class="d-inline"
                                                  onsubmit="return confirm('Hapus grade {{ $item->kode }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-body-secondary py-4">Tidak ada data grade.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $daftar->links() }}</div>
        </div>
    </div>
@endsection
