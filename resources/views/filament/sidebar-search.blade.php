<div
    x-data="{
        filter() {
            const term = this.$refs.search.value.toLowerCase().trim()
            const nav = this.$el.closest('.fi-sidebar-nav') ?? document

            nav.querySelectorAll('.fi-sidebar-item').forEach((item) => {
                item.style.display = term === '' || item.textContent.toLowerCase().includes(term)
                    ? ''
                    : 'none'
            })

            nav.querySelectorAll('.fi-sidebar-group').forEach((group) => {
                const hasMatch = [...group.querySelectorAll('.fi-sidebar-item')]
                    .some((item) => item.style.display !== 'none')

                group.style.display = term === '' || hasMatch ? '' : 'none'
            })
        },
    }"
    class="wms-sidebar-search"
>
    <x-filament::input.wrapper>
        <x-filament::input
            type="search"
            x-ref="search"
            x-on:input.debounce.200ms="filter()"
            placeholder="Cari menu…"
            aria-label="Cari menu"
        />
    </x-filament::input.wrapper>
</div>
