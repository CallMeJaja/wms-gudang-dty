@extends('layouts.app')

@section('title', 'Detail — Stock Opname')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('opname.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Stock Opname
            </a>
        </div>
        <p class="text-secondary small mb-0">
            {{ $opname->nomor_opname }} · {{ $opname->barang->kode_barang }} · Merge {{ $opname->merge }} · Grade {{ $opname->grade->kode }}
            · {{ $opname->tanggal->format('d-m-Y') }}
        </p>
    </div>

    <div class="row g-3 col-lg-8">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="small text-body-secondary">Stok Sistem</div>
                <div class="fs-5">{{ number_format((float) $opname->stok_sistem_qty, 2, ',', '.') }} kg</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="small text-body-secondary">Stok Fisik</div>
                <div class="fs-5">{{ number_format((float) $opname->stok_fisik_qty, 2, ',', '.') }} kg</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="small text-body-secondary">Selisih</div>
                <div class="fs-5 fw-semibold @if((float) $opname->selisih_qty > 0) text-success @elseif((float) $opname->selisih_qty < 0) text-danger @else text-secondary @endif">
                    {{ number_format((float) $opname->selisih_qty, 2, ',', '.') }} kg
                </div>
            </div></div>
        </div>
    </div>

    <dl class="row mt-3 col-lg-8">
        <dt class="col-sm-4">Penyesuaian</dt>
        <dd class="col-sm-8">
            @if ($opname->transaksi_masuk_id)
                <span class="badge text-bg-success">ADJUSTMENT_IN</span>
                {{ $opname->transaksiMasuk->nomor_transaksi }}
            @elseif ($opname->transaksi_keluar_id)
                <span class="badge text-bg-danger">ADJUSTMENT_OUT</span>
                {{ $opname->transaksiKeluar->nomor_transaksi }}
            @else
                <span class="text-secondary">Tidak ada (selisih nol).</span>
            @endif
        </dd>
        <dt class="col-sm-4">Keterangan</dt>
        <dd class="col-sm-8">{{ $opname->keterangan ?? '—' }}</dd>
        <dt class="col-sm-4">Dicatat oleh</dt>
        <dd class="col-sm-8">{{ $opname->pembuat?->name ?? '—' }}</dd>
    </dl>
@endsection
