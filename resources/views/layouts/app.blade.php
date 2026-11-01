{{--
    Layout utama aplikasi WMS Gudang DTY.
    Sidebar kiri (offcanvas di mobile) + topbar ramping.
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
    <div class="wms-shell">
        @include('layouts.sidebar')
        <div class="wms-main">
            @include('layouts.topbar')
            <main class="wms-content">
                @include('layouts.flash')
                @yield('content')
            </main>
        </div>
    </div>
@else
    <main class="container py-5">
        @include('layouts.flash')
        @yield('content')
    </main>
@endauth

@stack('scripts')
</body>
</html>
