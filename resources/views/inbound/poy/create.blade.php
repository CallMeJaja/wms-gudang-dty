@extends('layouts.app')

@section('title', 'Transaksi Baru — Penerimaan POY (IPCI)')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('inbound.poy.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Penerimaan POY (IPCI)
            </a>
        </div>
        <p class="text-secondary small mb-0">
            Disimpan sebagai <strong>DRAF</strong> terlebih dahulu; stok belum berubah sampai dokumen diposting/diterbitkan.
        </p>
    </div>

    <form method="POST" action="{{ route('inbound.poy.store') }}" data-wms-inbound-form="poy">
        @csrf
        @include('inbound._form', [
            'jenisForm' => 'poy',
            'labelJudul' => 'Baris Penerimaan POY',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan sebagai Draf
            </button>
            <a href="{{ route('inbound.poy.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
