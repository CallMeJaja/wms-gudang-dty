@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('content')
    <div class="mb-3">
        <p class="text-secondary small mb-0">Kode singkat unik, mis. BOX / BOBBIN / KG.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.satuan.store') }}">
                @csrf
                @include('master.satuan._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                    <a href="{{ route('master.satuan.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
