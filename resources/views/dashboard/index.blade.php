@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0">Dashboard</h1>
            <p class="text-body-secondary small mb-0">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }} &middot; Shift berjalan:
                @php
                    $jam = now()->format('H:i');
                    $shiftAktif = collect(config('wms.shift'))->first(
                        fn ($s) => $jam >= $s['mulai'] && $jam < $s['selesai']
                    );
                @endphp
                @if ($shiftAktif)
                    <span class="badge text-bg-primary">
                        Shift {{ collect(config('wms.shift'))->search($shiftAktif) }}
                        ({{ $shiftAktif['mulai'] }}–{{ $shiftAktif['selesai'] }})
                    </span>
                @else
                    <span class="badge text-bg-secondary">Shift 3 (22:00–06:00)</span>
                @endif
            </p>
        </div>
        @if ($bolehRilis && auth()->user()->canWrite())
            <form method="POST" action="{{ route('dashboard.rilis-buffer') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger"
                        data-wms-confirm="Rilis seluruh buffer Minggu ke stok aktif?">
                    <i class="bi bi-box-arrow-up me-1"></i> Rilis Buffer Minggu
                </button>
            </form>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card wms-stat {{ $aktifAman ? 'wms-stat--ok' : 'wms-stat--danger' }} h-100">
                <div class="card-body">
                    <div class="text-body-secondary small">Stok Aktif POY</div>
                    <div class="fs-4 fw-semibold wms-num">{{ number_format($aktifPallet, 2, ',', '.') }} / {{ $minPallet }} pallet</div>
                    <div class="small text-body-secondary wms-num">{{ number_format($aktifKg, 2, ',', '.') }} kg</div>
                    @if ($aktifAman)
                        <div class="small text-success">Aman — di atas kebutuhan 1 hari kerja mesin.</div>
                    @else
                        <div class="small text-danger">Stok aktif di bawah kebutuhan 1 hari kerja mesin — risiko stop produksi hari ini.</div>
                    @endif
                    <div class="small text-body-secondary">
                        Ambang aman: {{ $minPallet }} pallet
                        ({{ number_format($minKg, 0, ',', '.') }} kg)
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php
                $kelasBuffer = $statusBuffer->level === 'SIAP' ? 'wms-stat--ok' : ($statusBuffer->level === 'TERLAMBAT' ? 'wms-stat--danger' : 'wms-stat--warn');
                $kelasBar = $statusBuffer->level === 'SIAP' ? 'bg-success' : ($statusBuffer->level === 'TERLAMBAT' ? 'bg-danger' : 'bg-warning');
            @endphp
            <div class="card wms-stat {{ $kelasBuffer }} h-100">
                <div class="card-body">
                    <div class="text-body-secondary small">
                        Buffer Minggu
                        <span class="badge {{ $statusBuffer->level === 'SIAP' ? 'text-bg-success' : ($statusBuffer->level === 'TERLAMBAT' ? 'text-bg-danger' : 'text-bg-warning') }}">
                            {{ $statusBuffer->label }}
                        </span>
                    </div>
                    <div class="fs-4 fw-semibold wms-num">{{ number_format($statusBuffer->bufferPallet, 2, ',', '.') }} / {{ $statusBuffer->targetPallet }} pallet</div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar {{ $kelasBar }}" style="width: {{ min(100, $progresBuffer) }}%"></div>
                    </div>
                    <div class="small text-body-secondary mt-1">{{ $statusBuffer->pesan }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card wms-stat h-100">
                <div class="card-body">
                    <div class="text-body-secondary small">Acuan Pallet</div>
                    <div class="fs-6 fw-semibold">POY {{ config('wms.pallet.poy_kg') }} kg/pallet</div>
                    <div class="fs-6 fw-semibold">DTY {{ number_format(config('wms.pallet.dty_kg'), 0, ',', '.') }} kg/pallet</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6">Stok Harian 30 Hari (kg)</h2>
            <div style="height: 260px;"><canvas id="grafikStok"></canvas></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6">Ringkasan per Kategori (kg)</h2>
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Kategori</th><th class="text-end">Aktif</th><th class="text-end">Buffer</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($ringkasan as $baris)
                                <tr>
                                    <td>{{ $baris['kode'] }}</td>
                                    <td class="wms-num">{{ number_format($baris['aktifKg'], 2, ',', '.') }}</td>
                                    <td class="wms-num">{{ number_format($baris['bufferKg'], 2, ',', '.') }}</td>
                                    <td class="wms-num">{{ number_format($baris['totalKg'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6">Alert ({{ count($alert) }})</h2>
                    @if (count($alert) === 0)
                        <p class="small text-body-secondary mb-0">Tidak ada alert — semua indikator normal.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach ($alert as $item)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $item['level'] === 'danger' ? 'text-bg-danger' : 'text-bg-warning' }}">
                                                {{ $item['level'] === 'danger' ? 'Merah' : 'Kuning' }}
                                            </span>
                                        </td>
                                        <td class="small">{{ $item['pesan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    WmsChart.line('#grafikStok', {
        labels: @json($grafik['labels']),
        datasets: [
            { label: 'POY masuk', data: @json($grafik['masukPoy']) },
            { label: 'POY keluar', data: @json($grafik['keluarPoy']) },
            { label: 'DTY masuk', data: @json($grafik['masukDty']) },
            { label: 'DTY keluar', data: @json($grafik['keluarDty']) },
        ],
    });
});
</script>
@endpush
