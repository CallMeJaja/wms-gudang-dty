{{--
    Sidebar navigasi WMS — dipakai layouts/app.blade.php.
    Offcanvas di mobile (offcanvas-lg), sticky di desktop.
    Visibilitas: opname hanya admin. Index lain boleh dibaca semua role
    yang login (tulis dibatasi via middleware/policy + @can di tombol).
--}}
@php($u = auth()->user())
@php($gMasuk = request()->routeIs('inbound.*'))
@php($gKeluar = request()->routeIs('outbound.*'))
@php($gMaster = request()->routeIs('master.*'))
@php($gLaporan = request()->routeIs('laporan.*'))
@php($gLain = request()->routeIs('opname.*'))
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

        <button type="button" class="wms-label wms-group-toggle" data-bs-toggle="collapse" data-bs-target="#wmsGroupMasuk" aria-expanded="{{ $gMasuk ? 'true' : 'false' }}" aria-controls="wmsGroupMasuk">
            Transaksi Masuk <i class="bi bi-chevron-down wms-chevron"></i>
        </button>
        <div class="collapse {{ $gMasuk ? 'show' : '' }} wms-submenu" id="wmsGroupMasuk" data-wms-group="masuk" data-wms-active="{{ $gMasuk ? '1' : '0' }}">
            <a href="{{ route('inbound.poy.index') }}" class="wms-link {{ request()->routeIs('inbound.poy.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> POY
            </a>
            <a href="{{ route('inbound.pendukung.index') }}" class="wms-link {{ request()->routeIs('inbound.pendukung.*') ? 'active' : '' }}">
                <i class="bi bi-box2-heart"></i> Pendukung
            </a>
            <a href="{{ route('inbound.dty.index') }}" class="wms-link {{ request()->routeIs('inbound.dty.*') ? 'active' : '' }}">
                <i class="bi bi-gear-wide-connected"></i> DTY
            </a>
        </div>

        <button type="button" class="wms-label wms-group-toggle" data-bs-toggle="collapse" data-bs-target="#wmsGroupKeluar" aria-expanded="{{ $gKeluar ? 'true' : 'false' }}" aria-controls="wmsGroupKeluar">
            Transaksi Keluar <i class="bi bi-chevron-down wms-chevron"></i>
        </button>
        <div class="collapse {{ $gKeluar ? 'show' : '' }} wms-submenu" id="wmsGroupKeluar" data-wms-group="keluar" data-wms-active="{{ $gKeluar ? '1' : '0' }}">
            <a href="{{ route('outbound.poy.index') }}" class="wms-link {{ request()->routeIs('outbound.poy.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> POY ke Produksi
            </a>
            <a href="{{ route('outbound.surat-jalan.index') }}" class="wms-link {{ request()->routeIs('outbound.surat-jalan.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Surat Jalan
            </a>
            <a href="{{ route('outbound.scrap.index') }}" class="wms-link {{ request()->routeIs('outbound.scrap.*') ? 'active' : '' }}">
                <i class="bi bi-trash3"></i> Scrap / Reject
            </a>
        </div>

        <button type="button" class="wms-label wms-group-toggle" data-bs-toggle="collapse" data-bs-target="#wmsGroupMaster" aria-expanded="{{ $gMaster ? 'true' : 'false' }}" aria-controls="wmsGroupMaster">
            Master Data <i class="bi bi-chevron-down wms-chevron"></i>
        </button>
        <div class="collapse {{ $gMaster ? 'show' : '' }} wms-submenu" id="wmsGroupMaster" data-wms-group="master" data-wms-active="{{ $gMaster ? '1' : '0' }}">
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
        </div>

        <button type="button" class="wms-label wms-group-toggle" data-bs-toggle="collapse" data-bs-target="#wmsGroupLaporan" aria-expanded="{{ $gLaporan ? 'true' : 'false' }}" aria-controls="wmsGroupLaporan">
            Laporan <i class="bi bi-chevron-down wms-chevron"></i>
        </button>
        <div class="collapse {{ $gLaporan ? 'show' : '' }} wms-submenu" id="wmsGroupLaporan" data-wms-group="laporan" data-wms-active="{{ $gLaporan ? '1' : '0' }}">
            <a href="{{ route('laporan.keluar-masuk.index') }}" class="wms-link {{ request()->routeIs('laporan.keluar-masuk.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Keluar-Masuk
            </a>
            <a href="{{ route('laporan.kartu-stok.index') }}" class="wms-link {{ request()->routeIs('laporan.kartu-stok.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Kartu Stok
            </a>
            <a href="{{ route('laporan.rendemen.index') }}" class="wms-link {{ request()->routeIs('laporan.rendemen.*') ? 'active' : '' }}">
                <i class="bi bi-pie-chart"></i> Rendemen
            </a>
        </div>

        @if ($u && $u->isAdmin())
            <button type="button" class="wms-label wms-group-toggle" data-bs-toggle="collapse" data-bs-target="#wmsGroupLain" aria-expanded="{{ $gLain ? 'true' : 'false' }}" aria-controls="wmsGroupLain">
                Lainnya <i class="bi bi-chevron-down wms-chevron"></i>
            </button>
            <div class="collapse {{ $gLain ? 'show' : '' }} wms-submenu" id="wmsGroupLain" data-wms-group="lain" data-wms-active="{{ $gLain ? '1' : '0' }}">
                <a href="{{ route('opname.index') }}" class="wms-link {{ request()->routeIs('opname.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i> Stock Opname
                </a>
            </div>
        @endif
    </nav>
</div>
