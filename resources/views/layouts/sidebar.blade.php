{{--
    Sidebar navigasi WMS — dipakai layouts/app.blade.php.
    Offcanvas di mobile (offcanvas-lg), statis di desktop.
    Visibilitas: opname hanya admin. Index lain boleh dibaca semua role
    yang login (tulis dibatasi via middleware/policy + @can di tombol).
--}}
@php($u = auth()->user())
<div class="offcanvas-lg offcanvas-start wms-sidebar wms-no-print" tabindex="-1" id="wmsSidebar" aria-labelledby="wmsSidebarLabel">
    <div class="wms-brand">
        <a href="{{ route('dashboard') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <span class="wms-brand-icon"><i class="bi bi-box-seam"></i></span>
            <span>
                <span class="d-block fw-semibold text-dark lh-1">WMS Gudang DTY</span>
                <span class="d-block text-body-secondary" style="font-size:.72rem">PEI &middot; Indorama</span>
            </span>
        </a>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#wmsSidebar" aria-label="Tutup"></button>
    </div>

    <nav class="wms-nav" aria-label="Navigasi utama">
        <a href="{{ route('dashboard') }}" class="wms-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="wms-label">Transaksi Masuk</div>
        <a href="{{ route('inbound.poy.index') }}" class="wms-link {{ request()->routeIs('inbound.poy.*') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i> POY
        </a>
        <a href="{{ route('inbound.pendukung.index') }}" class="wms-link {{ request()->routeIs('inbound.pendukung.*') ? 'active' : '' }}">
            <i class="bi bi-box2-heart"></i> Pendukung
        </a>
        <a href="{{ route('inbound.dty.index') }}" class="wms-link {{ request()->routeIs('inbound.dty.*') ? 'active' : '' }}">
            <i class="bi bi-gear-wide-connected"></i> DTY
        </a>

        <div class="wms-label">Transaksi Keluar</div>
        <a href="{{ route('outbound.poy.index') }}" class="wms-link {{ request()->routeIs('outbound.poy.*') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i> POY ke Produksi
        </a>
        <a href="{{ route('outbound.surat-jalan.index') }}" class="wms-link {{ request()->routeIs('outbound.surat-jalan.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Surat Jalan
        </a>
        <a href="{{ route('outbound.scrap.index') }}" class="wms-link {{ request()->routeIs('outbound.scrap.*') ? 'active' : '' }}">
            <i class="bi bi-trash3"></i> Scrap / Reject
        </a>

        <div class="wms-label">Master Data</div>
        <a href="{{ route('master.barang.index') }}" class="wms-link {{ request()->routeIs('master.barang.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Barang
        </a>
        <a href="{{ route('master.partner.index') }}" class="wms-link {{ request()->routeIs('master.partner.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Partner
        </a>
        <a href="{{ route('master.grade.index') }}" class="wms-link {{ request()->routeIs('master.grade.*') ? 'active' : '' }}">
            <i class="bi bi-award"></i> Grade
        </a>
        <a href="{{ route('master.konversi.index') }}" class="wms-link {{ request()->routeIs('master.konversi.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Konversi
        </a>
        <a href="{{ route('master.kategori.index') }}" class="wms-link {{ request()->routeIs('master.kategori.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Kategori
        </a>
        <a href="{{ route('master.satuan.index') }}" class="wms-link {{ request()->routeIs('master.satuan.*') ? 'active' : '' }}">
            <i class="bi bi-rulers"></i> Satuan
        </a>

        <div class="wms-label">Laporan</div>
        <a href="{{ route('laporan.keluar-masuk.index') }}" class="wms-link {{ request()->routeIs('laporan.keluar-masuk.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Keluar-Masuk
        </a>
        <a href="{{ route('laporan.kartu-stok.index') }}" class="wms-link {{ request()->routeIs('laporan.kartu-stok.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Kartu Stok
        </a>
        <a href="{{ route('laporan.rendemen.index') }}" class="wms-link {{ request()->routeIs('laporan.rendemen.*') ? 'active' : '' }}">
            <i class="bi bi-pie-chart"></i> Rendemen
        </a>

        @if ($u && $u->isAdmin())
            <div class="wms-label">Lainnya</div>
            <a href="{{ route('opname.index') }}" class="wms-link {{ request()->routeIs('opname.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> Stock Opname
            </a>
        @endif
    </nav>
</div>
