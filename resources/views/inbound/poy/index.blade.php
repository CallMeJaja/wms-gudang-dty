@extends('layouts.app')

@section('title', 'Penerimaan POY (IPCI)')

@section('content')
    @include('inbound._index', [
        'jenisForm' => 'poy',
        'jenisMasuk' => App\Enums\JenisMasuk::PoyIpci,
        'judul' => 'Penerimaan POY (IPCI)',
        'deskripsi' => 'Penerimaan bahan baku POY dari IPCI — DRAF lalu POSTING.',
        'ruteIndex' => 'inbound.poy.index',
        'kolomNomor' => 'Ref. Dokumen',
    ])
@endsection
