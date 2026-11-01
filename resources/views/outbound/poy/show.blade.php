@extends('layouts.app')

@section('title', 'Detail — Pengeluaran POY ke Produksi')

@section('content')
    @include('outbound._show', [
        'jenisForm' => 'poy',
        'jenisKeluar' => App\Enums\JenisKeluar::PoyProduksi,
        'judul' => 'Pengeluaran POY ke Produksi',
        'namaAksi' => 'posting',
        'labelAksi' => 'Posting',
    ])
@endsection
