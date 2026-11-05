<x-filament-panels::page>
    <p style="font-size: 0.8rem; color: var(--gray-500); margin: 0;">
        Rendemen = (DTY keluar via Surat Jalan ÷ POY masuk dari IPCI) × 100%.
    </p>

    <x-filament::section
        icon="heroicon-o-funnel"
        description="{{ $periodeDefault ? 'Default: 30 hari terakhir. Sesuaikan periode bila perlu.' : 'Periode terpilih. Sesuaikan bila perlu.' }}"
    >
        <x-slot name="heading">Filter</x-slot>

        <form method="GET" action="{{ url()->current() }}">
            <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Dari</span>
                    <x-filament::input.wrapper style="min-width: 10rem;">
                        <x-filament::input type="date" name="dari" value="{{ $filter['dari'] }}" />
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Sampai</span>
                    <x-filament::input.wrapper style="min-width: 10rem;">
                        <x-filament::input type="date" name="sampai" value="{{ $filter['sampai'] }}" />
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; align-items: center; gap: 0.75rem; padding-bottom: 0.125rem;">
                    <x-filament::button type="submit">Terapkan</x-filament::button>
                    <a
                        href="{{ url()->current() }}"
                        style="font-size: 0.8rem; color: var(--primary-600); text-decoration: none;"
                    >
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </x-filament::section>

    <x-filament::section
        heading="Ringkasan Rendemen"
        description="{{ $periodeDefault ? 'POY masuk dari IPCI dibandingkan dengan DTY keluar lewat Surat Jalan pada 30 hari terakhir.' : 'POY masuk dari IPCI dibandingkan dengan DTY keluar lewat Surat Jalan pada periode terpilih.' }}"
        icon="heroicon-o-chart-bar"
    >
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">POY Masuk</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ number_format($hasil['totalPoy'], 2, ',', '.') }} kg
                </div>
                <div style="font-size: 0.72rem; color: var(--gray-500);">
                    {{ number_format($hasil['totalPoy'] / 1000, 2, ',', '.') }} ton
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">DTY Keluar</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ number_format($hasil['totalDty'], 2, ',', '.') }} kg
                </div>
                <div style="font-size: 0.72rem; color: var(--gray-500);">
                    {{ number_format($hasil['totalDty'] / 1000, 2, ',', '.') }} ton
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Rendemen</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, ',', '.').'%' }}
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Waste / Selisih</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ number_format($hasil['wasteKg'], 2, ',', '.') }} kg
                </div>
                <div style="font-size: 0.72rem; color: var(--gray-500);">
                    {{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, ',', '.').'%' }}
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Target Bulanan</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ number_format($hasil['targetKg'] / 1000, 2, ',', '.') }} ton
                </div>
                <div style="font-size: 0.72rem; color: var(--gray-500);">
                    {{ number_format($hasil['targetKg'], 2, ',', '.') }} kg
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Pencapaian</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ $hasil['pencapaianTarget'] === null ? '-' : number_format($hasil['pencapaianTarget'], 2, ',', '.').'%' }}
                </div>
                <div style="font-size: 0.72rem; color: var(--gray-500);">
                    Selisih target: {{ $hasil['selisihTargetKg'] > 0 ? '+' : '' }}{{ number_format($hasil['selisihTargetKg'], 2, ',', '.') }} kg
                </div>
            </div>
        </div>

        @if ($hasil['surplus'])
            <div style="display: flex; align-items: flex-start; gap: 0.5rem; border: 1px solid var(--danger-200); background: var(--danger-50); color: var(--danger-700); border-radius: 0.5rem; padding: 0.75rem; font-size: 0.78rem; line-height: 1.5; margin-bottom: 1rem;">
                <x-filament::icon
                    icon="heroicon-o-exclamation-triangle"
                    style="width: 1.1rem; height: 1.1rem; flex-shrink: 0;"
                />
                <div>
                    DTY keluar lebih besar dari POY masuk. Data surplus/tidak valid — periksa transaksi pada periode ini.
                </div>
            </div>
        @endif

        <div style="display: flex; align-items: flex-start; gap: 0.5rem; border: 1px solid var(--gray-200); background: var(--gray-50); color: var(--gray-600); border-radius: 0.5rem; padding: 0.75rem; font-size: 0.75rem; line-height: 1.5;">
            <x-filament::icon
                icon="heroicon-o-information-circle"
                style="width: 1.1rem; height: 1.1rem; flex-shrink: 0; color: var(--primary-600);"
            />
            <div>
                <div>{{ $disclaimerRingkas }}</div>
                <details style="margin-top: 0.25rem;">
                    <summary style="color: var(--primary-600); cursor: pointer;">Selengkapnya</summary>
                    <div style="margin-top: 0.25rem;">{{ $disclaimerLengkap }}</div>
                </details>
            </div>
        </div>

    </x-filament::section>
</x-filament-panels::page>
