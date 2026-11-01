@extends('layouts.app')

@section('title', 'Penerimaan Item Pendukung')

@section('content')
    @include('inbound._index', [
        'jenisForm' => 'pendukung',
        'jenisMasuk' => App\Enums\JenisMasuk::PendukungGatepass,
        'judul' => 'Penerimaan Item Pendukung',
        'deskripsi' => 'Penerimaan packaging & consumables dari Store IPCI via Gate Pass — DRAF lalu TERBITKAN.',
        'ruteIndex' => 'inbound.pendukung.index',
        'kolomNomor' => 'Nomor Gate Pass',
    ])
@endsection
