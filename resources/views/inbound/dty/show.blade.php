@extends('layouts.app')

@section('title', 'Detail — Penerimaan DTY Produksi')

@section('content')
    @include('inbound._show', [
        'jenisForm' => 'dty',
        'jenisMasuk' => App\Enums\JenisMasuk::DtyProduksi,
        'judul' => 'Penerimaan DTY Produksi',
        'namaAksi' => 'posting',
        'labelAksi' => 'Posting',
    ])
@endsection
