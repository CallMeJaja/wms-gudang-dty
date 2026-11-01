@extends('layouts.app')

@section('title', 'Transaksi Baru — Penerimaan DTY Produksi')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('inbound.dty.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Penerimaan DTY Produksi
            </a>
        </div>
        <h1 class="h4 mb-0"><i class="bi bi-plus-circle me-1"></i> Transaksi Baru — Penerimaan DTY Produksi</h1>
        <p class="text-body-secondary small mb-0">
            Disimpan sebagai <strong>DRAF</strong> terlebih dahulu; stok belum berubah sampai dokumen diposting/diterbitkan.
        </p>
    </div>

    <form method="POST" action="{{ route('inbound.dty.store') }}" data-wms-inbound-form="dty">
        @csrf
        @include('inbound._form', [
            'jenisForm' => 'dty',
            'labelJudul' => 'Baris Penerimaan DTY',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan sebagai Draf
            </button>
            <a href="{{ route('inbound.dty.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
