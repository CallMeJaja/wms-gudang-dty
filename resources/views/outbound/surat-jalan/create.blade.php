@extends('layouts.app')

@section('title', 'Transaksi Baru — Surat Jalan DTY')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('outbound.surat-jalan.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Surat Jalan DTY
            </a>
        </div>
        <p class="text-secondary small mb-0">
            Disimpan sebagai <strong>DRAF</strong> terlebih dahulu; stok belum berkurang sampai diterbitkan.
        </p>
    </div>

    <form method="POST" action="{{ route('outbound.surat-jalan.store') }}" data-wms-outbound-form="surat-jalan">
        @csrf
        @include('outbound._form', [
            'jenisForm' => 'surat-jalan',
            'labelJudul' => 'Baris Surat Jalan DTY',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan sebagai Draf
            </button>
            <a href="{{ route('outbound.surat-jalan.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
