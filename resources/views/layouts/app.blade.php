{{--
    Layout utama aplikasi WMS Gudang DTY.
    Bootstrap 5.3 + Bootstrap Icons via Vite (bukan CDN).
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">

@auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark wms-no-print">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">
                <i class="bi bi-box-seam me-1"></i> {{ config('app.name') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUtama">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navUtama">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('inbound.*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Transaksi Masuk
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('inbound.poy.*') ? 'active' : '' }}"
                                   href="{{ route('inbound.poy.index') }}">
                                    <i class="bi bi-boxes me-1"></i> Penerimaan POY
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('inbound.pendukung.*') ? 'active' : '' }}"
                                   href="{{ route('inbound.pendukung.index') }}">
                                    <i class="bi bi-box2-heart me-1"></i> Item Pendukung
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('inbound.dty.*') ? 'active' : '' }}"
                                   href="{{ route('inbound.dty.index') }}">
                                    <i class="bi bi-gear-wide-connected me-1"></i> DTY Produksi
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('outbound.*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-arrow-up me-1"></i> Transaksi Keluar
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('outbound.poy.*') ? 'active' : '' }}"
                                   href="{{ route('outbound.poy.index') }}">
                                    <i class="bi bi-boxes me-1"></i> POY ke Produksi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('outbound.surat-jalan.*') ? 'active' : '' }}"
                                   href="{{ route('outbound.surat-jalan.index') }}">
                                    <i class="bi bi-truck me-1"></i> Surat Jalan DTY
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('outbound.scrap.*') ? 'active' : '' }}"
                                   href="{{ route('outbound.scrap.index') }}">
                                    <i class="bi bi-trash3 me-1"></i> Scrap / Reject
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('laporan.*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-bar-graph me-1"></i> Laporan
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('laporan.keluar-masuk.*') ? 'active' : '' }}"
                                   href="{{ route('laporan.keluar-masuk.index') }}">
                                    <i class="bi bi-arrow-left-right me-1"></i> Keluar-Masuk
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('laporan.kartu-stok.*') ? 'active' : '' }}"
                                   href="{{ route('laporan.kartu-stok.index') }}">
                                    <i class="bi bi-journal-text me-1"></i> Kartu Stok
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('laporan.rendemen.*') ? 'active' : '' }}"
                                   href="{{ route('laporan.rendemen.index') }}">
                                    <i class="bi bi-pie-chart me-1"></i> Rendemen POY vs DTY
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('master.*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-database me-1"></i> Master Data
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('master.barang.*') ? 'active' : '' }}"
                                   href="{{ route('master.barang.index') }}">
                                    <i class="bi bi-box-seam me-1"></i> Barang
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('master.partner.*') ? 'active' : '' }}"
                                   href="{{ route('master.partner.index') }}">
                                    <i class="bi bi-people me-1"></i> Partner
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('master.grade.*') ? 'active' : '' }}"
                                   href="{{ route('master.grade.index') }}">
                                    <i class="bi bi-award me-1"></i> Grade
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('master.konversi.*') ? 'active' : '' }}"
                                   href="{{ route('master.konversi.index') }}">
                                    <i class="bi bi-arrow-left-right me-1"></i> Konversi
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('master.kategori.*') ? 'active' : '' }}"
                                   href="{{ route('master.kategori.index') }}">
                                    <i class="bi bi-tags me-1"></i> Kategori
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('master.satuan.*') ? 'active' : '' }}"
                                   href="{{ route('master.satuan.index') }}">
                                    <i class="bi bi-rulers me-1"></i> Satuan
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item me-lg-3">
                        <span class="badge text-bg-secondary">
                            <i class="bi bi-person-badge me-1"></i>{{ auth()->user()->role->label() }}
                        </span>
                    </li>
                    <li class="nav-item me-lg-3 text-white-50 small">
                        {{ auth()->user()->name }}
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-light" type="submit">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endauth

<main class="@auth container-fluid py-4 @else container py-5 @endauth">
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

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
