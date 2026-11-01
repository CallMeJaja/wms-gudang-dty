{{--
    Partial index pengeluaran (F3).

    Variabel: $daftar, $filter, $daftarStatus, $jenisForm, $jenisKeluar,
              $judul, $deskripsi, $ruteIndex, $kolomNomor
--}}

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <p class="text-secondary small mb-0">{{ $deskripsi }}</p>
    <div>
        @can('create', [App\Models\TransaksiKeluar::class, $jenisKeluar])
            <a href="{{ route("outbound.{$jenisForm}.create") }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
            </a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route($ruteIndex) }}" class="row g-2 mb-3 align-items-end">
            <div class="col-sm-6 col-md-2">
                <label for="dari" class="form-label small mb-1">Tanggal Dari</label>
                <input type="date" id="dari" name="dari" class="form-control form-control-sm" value="{{ $filter['dari'] }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="sampai" class="form-label small mb-1">Sampai</label>
                <input type="date" id="sampai" name="sampai" class="form-control form-control-sm" value="{{ $filter['sampai'] }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="status" class="form-label small mb-1">Status</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="">— Semua —</option>
                    @foreach ($daftarStatus as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label for="cari" class="form-label small mb-1">Cari Nomor</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="cari" name="cari" class="form-control"
                           placeholder="Nomor transaksi / dokumen…" value="{{ $filter['cari'] }}">
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary" type="submit">Terapkan</button>
                <a href="{{ route($ruteIndex) }}" class="btn btn-sm btn-link">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nomor Transaksi</th>
                        <th>{{ $kolomNomor }}</th>
                        <th>Tanggal</th>
                        <th class="wms-num">Total Netto (Kg)</th>
                        <th class="text-center">Status</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $item)
                        <tr>
                            <td class="font-monospace small">{{ $item->nomor_transaksi }}</td>
                            @if ($jenisForm === 'surat-jalan')
                                @if ($item->nomor_surat_jalan)
                                    <td class="font-monospace small">{{ $item->nomor_surat_jalan }}</td>
                                @else
                                    <td><span class="badge text-bg-secondary">Belum terbit</span></td>
                                @endif
                            @else
                                <td>{{ $item->operator_nama ?? '—' }}</td>
                            @endif
                            <td>{{ $item->tanggal?->format('d-m-Y') }}</td>
                            <td class="wms-num">{{ number_format((float) ($item->total_netto ?? 0), 2, ',', '.') }}</td>
                            <td class="text-center">
                                @include('outbound._badge-status', ['status' => $item->status])
                            </td>
                            <td class="small text-body-secondary">
                                {{ $item->pembuat?->name ?? '—' }}<br>
                                {{ $item->created_at?->format('d-m-Y H:i') }}
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route("outbound.{$jenisForm}.show", $item) }}" class="btn btn-sm btn-outline-secondary"
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('update', [$item, $jenisKeluar])
                                    <a href="{{ route("outbound.{$jenisForm}.edit", $item) }}" class="btn btn-sm btn-outline-primary"
                                       title="Ubah draf">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-body-secondary py-4">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $daftar->links() }}</div>
    </div>
</div>
