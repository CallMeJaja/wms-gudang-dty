@extends('layouts.app')

@section('title', 'Konversi Satuan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-secondary small mb-0">Konversi kemasan → Kg. Barang POY tidak boleh memiliki konversi.</p>
        <div>
            @can('create', App\Models\KonversiSatuan::class)
                <a href="{{ route('master.konversi.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Konversi
                </a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('master.konversi.index') }}" class="row g-2 mb-3">
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
                        <a href="{{ route('master.konversi.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Barang</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th class="wms-num">Faktor</th>
                            <th class="text-center">Default</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="small">
                                    @if ($item->barang)
                                        <span class="font-monospace">{{ $item->barang->kode_barang }}</span>
                                    @else
                                        <span class="badge text-bg-light text-dark">Global</span>
                                    @endif
                                </td>
                                <td>{{ $item->dariSatuan?->kode ?? '—' }}</td>
                                <td>{{ $item->keSatuan?->kode ?? '—' }}</td>
                                <td class="wms-num">{{ rtrim(rtrim(number_format((float) $item->faktor, 4, ',', '.'), '0'), ',') }}</td>
                                <td class="text-center">
                                    @if ($item->is_default)
                                        <span class="badge text-bg-primary">Ya</span>
                                    @else
                                        <span class="text-body-tertiary">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @include('master._partials.actions', [
                                        'model' => $item,
                                        'routeBase' => 'master.konversi',
                                        'label' => 'konversi ini',
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary py-4">Belum ada konversi satuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $daftar->links() }}</div>
        </div>
    </div>
@endsection
