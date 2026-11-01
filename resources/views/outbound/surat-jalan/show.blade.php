@extends('layouts.app')

@section('title', 'Detail — Surat Jalan DTY')

@section('content')
    @include('outbound._show', [
        'jenisForm' => 'surat-jalan',
        'jenisKeluar' => App\Enums\JenisKeluar::DtySuratJalan,
        'judul' => 'Surat Jalan DTY',
        'namaAksi' => 'issue',
        'labelAksi' => 'Terbitkan',
        'cetakRoute' => 'outbound.surat-jalan.cetak',
    ])
@endsection
