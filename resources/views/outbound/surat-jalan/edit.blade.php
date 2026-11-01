@extends('layouts.app')

@section('title', 'Ubah Draf — Surat Jalan DTY')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('outbound.surat-jalan.show', $transaksi) }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali ke detail {{ $transaksi->nomor_transaksi }}
            </a>
        </div>
        <p class="text-secondary small mb-0">Hanya transaksi berstatus DRAF yang dapat diubah.</p>
    </div>

    <form method="POST" action="{{ route('outbound.surat-jalan.update', $transaksi) }}" data-wms-outbound-form="surat-jalan">
        @csrf
        @method('PUT')
        @include('outbound._form', [
            'jenisForm' => 'surat-jalan',
            'labelJudul' => 'Baris Surat Jalan DTY',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('outbound.surat-jalan.show', $transaksi) }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
