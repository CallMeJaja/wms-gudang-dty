{{--
    Topbar ramping WMS — dipakai layouts/app.blade.php.
--}}
@php($u = auth()->user())
<header class="wms-topbar wms-no-print">
    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#wmsSidebar" aria-controls="wmsSidebar" aria-label="Buka menu">
        <i class="bi bi-list"></i>
    </button>
    <h1 class="wms-page-title mb-0">@yield('title', 'Dashboard')</h1>
    <div class="ms-auto d-flex align-items-center gap-2">
        @if ($u)
            <span class="badge text-bg-light border fw-normal">
                <i class="bi bi-person-badge me-1"></i>{{ $u->role->label() }}
            </span>
            <span class="text-body-secondary small d-none d-sm-inline">{{ $u->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" type="submit" title="Keluar">
                    <i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-md-inline">Keluar</span>
                </button>
            </form>
        @endif
    </div>
</header>
