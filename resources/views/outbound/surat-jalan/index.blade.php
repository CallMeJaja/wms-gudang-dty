@extends('layouts.app')

@section('title', 'Surat Jalan DTY')

@section('content')
    @include('outbound._index', [
        'jenisForm' => 'surat-jalan',
        'jenisKeluar' => App\Enums\JenisKeluar::DtySuratJalan,
        'judul' => 'Surat Jalan DTY',
        'deskripsi' => 'Pengiriman DTY ke pelanggan — DRAF lalu TERBITKAN (ISSUED).',
        'ruteIndex' => 'outbound.surat-jalan.index',
        'kolomNomor' => 'No. Surat Jalan',
    ])
@endsection
