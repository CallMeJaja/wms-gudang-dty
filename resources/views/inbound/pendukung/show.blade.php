@extends('layouts.app')

@section('title', 'Detail — Penerimaan Item Pendukung')

@section('content')
    @include('inbound._show', [
        'jenisForm' => 'pendukung',
        'jenisMasuk' => App\Enums\JenisMasuk::PendukungGatepass,
        'judul' => 'Penerimaan Item Pendukung',
        'namaAksi' => 'issue',
        'labelAksi' => 'Terbitkan',
    ])
@endsection
