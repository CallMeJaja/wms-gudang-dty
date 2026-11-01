@extends('layouts.app')

@section('title', 'Pengeluaran Scrap / Reject DTY')

@section('content')
    @include('outbound._index', [
        'jenisForm' => 'scrap',
        'jenisKeluar' => App\Enums\JenisKeluar::DtyScrapReject,
        'judul' => 'Pengeluaran Scrap / Reject DTY',
        'deskripsi' => 'Pengeluaran DTY grade C / benang putus — DRAF lalu POSTING.',
        'ruteIndex' => 'outbound.scrap.index',
        'kolomNomor' => 'Operator',
    ])
@endsection
