@extends('layouts.app')

@section('title', 'Transaksi Baru — Pengeluaran Scrap DTY')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('outbound.scrap.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Pengeluaran Scrap / Reject DTY
            </a>
        </div>
        <p class="text-secondary small mb-0">
            Disimpan sebagai <strong>DRAF</strong> terlebih dahulu; stok belum berkurang sampai diposting.
            Catatan (tujuan/penerima) <strong>wajib</strong> diisi.
        </p>
    </div>

    <form method="POST" action="{{ route('outbound.scrap.store') }}" data-wms-outbound-form="scrap">
        @csrf
        @include('outbound._form', [
            'jenisForm' => 'scrap',
            'labelJudul' => 'Baris Pengeluaran Scrap',
        ])

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan sebagai Draf
            </button>
            <a href="{{ route('outbound.scrap.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection
