@extends('layouts.app')

@section('title', 'Detail — Pengeluaran Scrap DTY')

@section('content')
    @include('outbound._show', [
        'jenisForm' => 'scrap',
        'jenisKeluar' => App\Enums\JenisKeluar::DtyScrapReject,
        'judul' => 'Pengeluaran Scrap / Reject DTY',
        'namaAksi' => 'posting',
        'labelAksi' => 'Posting',
    ])
@endsection
