<div
    x-data="{
        q: '',
        filter() {
            const query = this.q.trim().toLowerCase()
            const nav = this.$el.closest('.fi-sidebar-nav') ?? document

            nav.querySelectorAll('.fi-sidebar-item').forEach((item) => {
                item.style.display = query === '' || item.textContent.toLowerCase().includes(query)
                    ? ''
                    : 'none'
            })

            nav.querySelectorAll('.fi-sidebar-group').forEach((group) => {
                const hasMatch = [...group.querySelectorAll('.fi-sidebar-item')]
                    .some((item) => item.style.display !== 'none')

                group.style.display = query === '' || hasMatch ? '' : 'none'
            })
        },
    }"
    class="wms-sidebar-search"
>
    <x-filament::input.wrapper>
        <x-filament::input
            type="search"
            x-model="q"
            x-on:input.debounce.200ms="filter()"
            placeholder="Cari menu…"
            aria-label="Cari menu"
        />
    </x-filament::input.wrapper>
</div>
