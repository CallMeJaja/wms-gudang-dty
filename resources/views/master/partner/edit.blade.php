@extends('layouts.app')

@section('title', 'Ubah Partner')

@section('content')
    <div class="mb-3">
        <p class="text-secondary small mb-0">{{ $partner->kode }} — {{ $partner->nama }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.partner.update', $partner) }}">
                @csrf
                @method('PUT')
                @include('master.partner._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('master.partner.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
