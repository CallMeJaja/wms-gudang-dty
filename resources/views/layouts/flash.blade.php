{{-- Flash sukses / error / peringatan / validasi — dipakai layouts/app.blade.php. --}}
@if (session('sukses'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Flash error tunggal (mis. AturanBisnisException dari aksi posting). --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-x-octagon me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Peringatan non-blokir (mis. gross DTY di luar toleransi grade, PRD §3.3). --}}
@if (session('peringatan') && count((array) session('peringatan')) > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i> Peringatan</div>
        <ul class="mb-0 mt-1">
            @foreach ((array) session('peringatan') as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any() && ! View::hasSection('abaikan-error-global'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Periksa kembali input Anda:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
