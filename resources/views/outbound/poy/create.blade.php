@extends('layouts.app')

@section('title', 'Transaksi Baru — Pengeluaran POY ke Produksi')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('outbound.poy.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Pengeluaran POY ke Produksi
            </a>
        </div>
        <h1 class="h4 mb-0"><i class="bi bi-plus-circle me-1"></i> Transaksi Baru — Pengeluaran POY ke Produksi</h1>
        <p class="text-body-secondary small mb-0">
            Disimpan sebagai <strong>DRAF</strong> terlebih dahulu; stok belum berkurang sampai diposting.
        </p>
    </div>

    <form method="POST" action="{{ route('outbound.poy.store') }}" data-wms-outbound-form="poy">
        @csrf
        @include('outbound._form', [
            'jenisForm' => 'poy',
            'labelJudul' => 'Baris Pengeluaran POY',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan sebagai Draf
            </button>
            <a href="{{ route('outbound.poy.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
