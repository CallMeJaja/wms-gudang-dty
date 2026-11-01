@extends('layouts.app')

@section('title', 'Detail — Penerimaan POY (IPCI)')

@section('content')
    @include('inbound._show', [
        'jenisForm' => 'poy',
        'jenisMasuk' => App\Enums\JenisMasuk::PoyIpci,
        'judul' => 'Penerimaan POY (IPCI)',
        'namaAksi' => 'posting',
        'labelAksi' => 'Posting',
    ])
@endsection
