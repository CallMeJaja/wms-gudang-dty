@extends('layouts.app')

@section('title', 'Masuk')
@section('abaikan-error-global', '1')

@section('content')
    <div class="row justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-box-seam text-primary" style="font-size: 2.5rem;"></i>
                        <h1 class="h5 mt-2 mb-1">{{ config('app.name') }}</h1>
                        <p class="text-body-secondary small mb-0">
                            Politeknik Enjinering Indorama &mdash; PT. Indorama Polychem Indonesia
                        </p>
                    </div>

                    @error('username')
                        <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text"
                                       class="form-control @error('username') is-invalid @enderror"
                                       id="username"
                                       name="username"
                                       value="{{ old('username') }}"
                                       autocomplete="username"
                                       autofocus
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       autocomplete="current-password"
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                   value="1" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="remember">Ingat saya</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-body-secondary small mt-3 mb-0">
                Operator gudang memakai <strong>satu akun bersama per shift</strong>.
            </p>
        </div>
    </div>
@endsection
