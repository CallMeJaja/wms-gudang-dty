@extends('layouts.app')

@section('title', 'Ubah Draf — Penerimaan POY (IPCI)')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('inbound.poy.show', $transaksi) }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali ke detail {{ $transaksi->nomor_transaksi }}
            </a>
        </div>
        <p class="text-secondary small mb-0">Hanya transaksi berstatus DRAF yang dapat diubah.</p>
    </div>

    <form method="POST" action="{{ route('inbound.poy.update', $transaksi) }}" data-wms-inbound-form="poy">
        @csrf
        @method('PUT')
        @include('inbound._form', [
            'jenisForm' => 'poy',
            'labelJudul' => 'Baris Penerimaan POY',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('inbound.poy.show', $transaksi) }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
