@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
    <div class="mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-plus-circle me-1"></i> Tambah Barang</h1>
        <p class="text-body-secondary small mb-0">Lengkapi data master barang baru.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.barang.store') }}">
                @csrf
                @include('master.barang._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                    <a href="{{ route('master.barang.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
