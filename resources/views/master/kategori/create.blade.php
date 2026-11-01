@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-plus-circle me-1"></i> Tambah Kategori Barang</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.kategori.store') }}">
                @csrf
                @include('master.kategori._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                    <a href="{{ route('master.kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
