@extends('layouts.app')

@section('title', 'Ubah Satuan')

@section('content')
    <div class="mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-pencil-square me-1"></i> Ubah Satuan</h1>
        <p class="text-body-secondary small mb-0">{{ $satuan->kode }} — {{ $satuan->nama }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.satuan.update', $satuan) }}">
                @csrf
                @method('PUT')
                @include('master.satuan._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('master.satuan.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
