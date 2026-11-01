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
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card wms-stat wms-stat--ok h-100">
                <div class="card-body">
                    <div class="text-body-secondary small">Stok Aktif POY</div>
                    <div class="fs-4 fw-semibold">— kg</div>
                    <div class="small text-body-secondary">
                        Ambang aman: {{ config('wms.ambang_poy.min_stok_aktif_pallet') }} pallet
                        ({{ number_format(config('wms.ambang_poy.min_stok_aktif_pallet') * config('wms.pallet.poy_kg'), 0, ',', '.') }} kg)
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card wms-stat wms-stat--warn h-100">
                <div class="card-body">
                    <div class="text-body-secondary small">Buffer Minggu</div>
                    <div class="fs-4 fw-semibold">— / {{ config('wms.ambang_poy.target_buffer_pallet') }} pallet</div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 0%"></div>
                    </div>
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

    <div class="card">
        <div class="card-body">
            <h2 class="h6">Tahap F0 — Fondasi Selesai</h2>
            <p class="text-body-secondary small">
                Konfigurasi konstanta bisnis, skema 17 tabel, model, seeder, dan autentikasi
                multi-role sudah siap. Widget buffer dan grafik stok harian akan diisi pada fase F4.
            </p>
            <ul class="list-unstyled small mb-0">
                <li><i class="bi bi-check-circle-fill text-success me-1"></i> Skema database &amp; integritas (CHECK, UNIQUE)</li>
                <li><i class="bi bi-check-circle-fill text-success me-1"></i> Master data &amp; seeder lengkap</li>
                <li><i class="bi bi-check-circle-fill text-success me-1"></i> Login multi-role (Admin / Operator / Manajer)</li>
                <li><i class="bi bi-circle text-body-tertiary me-1"></i> Master data &amp; RBAC (F1)</li>
                <li><i class="bi bi-circle text-body-tertiary me-1"></i> Inbound, Outbound, Dashboard, Laporan, Void/Opname</li>
            </ul>
        </div>
    </div>
@endsection
