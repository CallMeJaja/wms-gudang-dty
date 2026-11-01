<div class="wms-sidebar-collapse" x-data="{}">
    <span x-show="$store.sidebar.isOpen">
        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-chevron-double-left"
            icon-size="sm"
            label="Ciutkan sidebar"
            x-on:click="$store.sidebar.close()"
        />
    </span>

    <span x-show="! $store.sidebar.isOpen" x-cloak>
        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-chevron-double-right"
            icon-size="sm"
            label="Perluas sidebar"
            x-on:click="$store.sidebar.open()"
        />
    </span>
</div>
