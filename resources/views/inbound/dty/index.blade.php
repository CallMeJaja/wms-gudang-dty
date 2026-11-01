@extends('layouts.app')

@section('title', 'Penerimaan DTY Produksi')

@section('content')
    @include('inbound._index', [
        'jenisForm' => 'dty',
        'jenisMasuk' => App\Enums\JenisMasuk::DtyProduksi,
        'judul' => 'Penerimaan DTY Produksi',
        'deskripsi' => 'Penerimaan DTY hasil produksi dari area doffing — DRAF lalu POSTING.',
        'ruteIndex' => 'inbound.dty.index',
        'kolomNomor' => 'Merge POY Asal',
    ])
@endsection
