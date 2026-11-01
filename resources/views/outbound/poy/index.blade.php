@extends('layouts.app')

@section('title', 'Pengeluaran POY ke Produksi')

@section('content')
    @include('outbound._index', [
        'jenisForm' => 'poy',
        'jenisKeluar' => App\Enums\JenisKeluar::PoyProduksi,
        'judul' => 'Pengeluaran POY ke Produksi',
        'deskripsi' => 'Pengeluaran bahan baku POY ke lantai produksi — DRAF lalu POSTING.',
        'ruteIndex' => 'outbound.poy.index',
        'kolomNomor' => 'Operator',
    ])
@endsection
