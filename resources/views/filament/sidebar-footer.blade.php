<div class="wms-sidebar-utility">
    <button
        type="button"
        x-data="{}"
        x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
        x-bind:aria-expanded="$store.sidebar.isOpen"
        aria-controls="fi-main-sidebar"
        class="wms-sidebar-utility-btn"
    >
        <span class="wms-sidebar-utility-icon" x-show="$store.sidebar.isOpen">
            <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedChevronDoubleLeft" />
        </span>
        <span class="wms-sidebar-utility-icon" x-show="! $store.sidebar.isOpen" x-cloak>
            <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedChevronDoubleRight" />
        </span>
        <span class="wms-sidebar-utility-label" x-show="$store.sidebar.isOpen">Ciutkan</span>
        <span class="wms-sidebar-utility-label" x-show="! $store.sidebar.isOpen" x-cloak>Perluas</span>
    </button>

    <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="wms-sidebar-logout-form">
        @csrf
        <button type="submit" class="wms-sidebar-utility-btn wms-sidebar-logout-btn">
            <span class="wms-sidebar-utility-icon">
                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowRightOnRectangle" />
            </span>
            <span class="wms-sidebar-utility-label" x-show="$store.sidebar.isOpen">Keluar</span>
        </button>
    </form>
</div>
