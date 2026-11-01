@extends('layouts.app')

@section('title', 'Transaksi Baru — Penerimaan Item Pendukung')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('inbound.pendukung.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Penerimaan Item Pendukung
            </a>
        </div>
        <p class="text-secondary small mb-0">
            Disimpan sebagai <strong>DRAF</strong> terlebih dahulu; stok belum berubah sampai dokumen diposting/diterbitkan.
        </p>
    </div>

    <form method="POST" action="{{ route('inbound.pendukung.store') }}" data-wms-inbound-form="pendukung">
        @csrf
        @include('inbound._form', [
            'jenisForm' => 'pendukung',
            'labelJudul' => 'Baris Item Pendukung',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan sebagai Draf
            </button>
            <a href="{{ route('inbound.pendukung.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
