@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <p class="text-body-secondary small mb-1">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }} &middot; Shift berjalan:
                @php
                    $jam = now()->format('H:i');
                    $shiftAktif = collect(config('wms.shift'))->first(
                        fn ($s) => $jam >= $s['mulai'] && $jam < $s['selesai']
                    );
                @endphp
                @if ($shiftAktif)
                    <span class="badge text-bg-primary-subtle border border-primary-subtle text-primary-emphasis">
                        Shift {{ collect(config('wms.shift'))->search($shiftAktif) }}
                        ({{ $shiftAktif['mulai'] }}–{{ $shiftAktif['selesai'] }})
                    </span>
                @else
                    <span class="badge text-bg-secondary">Shift 3 (22:00–06:00)</span>
                @endif
            </p>
            <p class="text-body-secondary mb-0">Ringkasan stok, buffer, dan alert hari ini.</p>
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
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="wms-stat-label text-body-secondary">Stok Aktif POY</div>
                        <span class="wms-stat-icon"><i class="bi bi-boxes"></i></span>
                    </div>
                    <div class="wms-stat-value wms-num">{{ number_format($aktifPallet, 2, ',', '.') }} / {{ $minPallet }} pallet</div>
                    <div class="small text-body-secondary wms-num mt-1">{{ number_format($aktifKg, 2, ',', '.') }} kg</div>
                    <div class="progress wms-progress mt-3" style="height: 8px;">
                        <div class="progress-bar {{ $aktifAman ? 'bg-success' : 'bg-danger' }}" style="width: {{ $minPallet > 0 ? min(100, ($aktifPallet / $minPallet) * 100) : 0 }}%"></div>
                    </div>
                    @if ($aktifAman)
                        <div class="small text-success mt-2"><i class="bi bi-check-circle me-1"></i>Aman — di atas kebutuhan 1 hari kerja mesin.</div>
                    @else
                        <div class="small text-danger mt-2"><i class="bi bi-exclamation-triangle me-1"></i>Di bawah kebutuhan 1 hari kerja mesin — risiko stop produksi hari ini.</div>
                    @endif
                    <div class="small text-body-secondary mt-1">
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
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="wms-stat-label text-body-secondary">
                            Buffer Minggu
                            <span class="badge {{ $statusBuffer->level === 'SIAP' ? 'text-bg-success' : ($statusBuffer->level === 'TERLAMBAT' ? 'text-bg-danger' : 'text-bg-warning') }} ms-1">
                                {{ $statusBuffer->label }}
                            </span>
                        </div>
                        <span class="wms-stat-icon"><i class="bi bi-stack"></i></span>
                    </div>
                    <div class="wms-stat-value wms-num">{{ number_format($statusBuffer->bufferPallet, 2, ',', '.') }} / {{ $statusBuffer->targetPallet }} pallet</div>
                    <div class="progress wms-progress mt-3" style="height: 8px;">
                        <div class="progress-bar {{ $kelasBar }}" style="width: {{ min(100, $progresBuffer) }}%"></div>
                    </div>
                    <div class="small text-body-secondary mt-2">{{ $statusBuffer->pesan }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card wms-stat h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="wms-stat-label text-body-secondary">Acuan Pallet</div>
                        <span class="wms-stat-icon"><i class="bi bi-rulers"></i></span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="text-body-secondary small" style="width: 3.5rem;">POY</span>
                        <span class="fs-5 fw-semibold wms-num">{{ config('wms.pallet.poy_kg') }} <span class="fs-6 fw-normal text-body-secondary">kg/pallet</span></span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="text-body-secondary small" style="width: 3.5rem;">DTY</span>
                        <span class="fs-5 fw-semibold wms-num">{{ number_format(config('wms.pallet.dty_kg'), 0, ',', '.') }} <span class="fs-6 fw-normal text-body-secondary">kg/pallet</span></span>
                    </div>
                    <div class="small text-body-secondary mt-3">Acuan konversi pallet dari konfigurasi gudang.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
                <h2 class="h6 mb-0">Stok Harian 30 Hari <span class="fw-normal text-body-secondary">(kg)</span></h2>
                <span class="small text-body-secondary">POY &amp; DTY masuk vs keluar</span>
            </div>
            <div style="height: 260px;"><canvas id="grafikStok"></canvas></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h2 class="h6 mb-0">Ringkasan per Kategori <span class="fw-normal text-body-secondary">(kg)</span></h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr><th>Kategori</th><th class="wms-num">Aktif</th><th class="wms-num">Buffer</th><th class="wms-num">Total</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($ringkasan as $baris)
                                    <tr>
                                        <td><span class="badge text-bg-light border fw-medium">{{ $baris['kode'] }}</span></td>
                                        <td class="wms-num">{{ number_format($baris['aktifKg'], 2, ',', '.') }}</td>
                                        <td class="wms-num">{{ number_format($baris['bufferKg'], 2, ',', '.') }}</td>
                                        <td class="wms-num fw-semibold">{{ number_format($baris['totalKg'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h2 class="h6 mb-0">Alert</h2>
                        <span class="badge {{ count($alert) === 0 ? 'text-bg-success' : 'text-bg-warning' }} rounded-pill">{{ count($alert) }}</span>
                    </div>
                    @if (count($alert) === 0)
                        <p class="small text-body-secondary mb-0"><i class="bi bi-check-circle me-1"></i>Tidak ada alert — semua indikator normal.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    @foreach ($alert as $item)
                                        <tr>
                                            <td style="width: 4.5rem;">
                                                <span class="badge {{ $item['level'] === 'danger' ? 'text-bg-danger' : 'text-bg-warning' }} rounded-pill">
                                                    {{ $item['level'] === 'danger' ? 'Merah' : 'Kuning' }}
                                                </span>
                                            </td>
                                            <td class="small">{{ $item['pesan'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
