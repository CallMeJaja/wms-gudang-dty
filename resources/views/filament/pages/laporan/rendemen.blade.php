<x-filament-panels::page>
    <p style="font-size: 0.8rem; color: var(--gray-500); margin: 0;">
        Rendemen = (DTY masuk ÷ POY keluar) × 100.
    </p>

    <x-filament::section
        icon="heroicon-o-funnel"
        description="Sesuaikan periode dan parameter laporan."
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
        description="Perbandingan total POY keluar dan DTY masuk pada periode terpilih."
        icon="heroicon-o-chart-bar"
    >
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Total POY Keluar</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ number_format($hasil['totalPoy'], 2, ',', '.') }} kg
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Total DTY Masuk</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ number_format($hasil['totalDty'], 2, ',', '.') }} kg
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Rendemen</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, ',', '.').'%' }}
                </div>
            </div>

            <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
                <div style="font-size: 0.75rem; color: var(--gray-500);">Waste</div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, ',', '.').'%' }}
                </div>
            </div>
        </div>

        <h3 style="font-size: 0.9rem; font-weight: 600; margin: 0 0 0.5rem;">Per Merge POY Asal</h3>

        <div style="overflow-x: auto; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
            <table class="wms-report-table" style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                <thead style="background: var(--gray-50); color: var(--gray-500); text-align: left;">
                    <tr>
                        <th style="padding: 0.5rem 0.625rem;">Merge POY Asal</th>
                        <th style="padding: 0.5rem 0.625rem; text-align: right;">POY Keluar (Kg)</th>
                        <th style="padding: 0.5rem 0.625rem; text-align: right;">DTY Masuk (Kg)</th>
                        <th style="padding: 0.5rem 0.625rem; text-align: right;">Rendemen</th>
                        <th style="padding: 0.5rem 0.625rem; text-align: right;">Waste</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hasil['perMerge'] as $baris)
                        <tr style="border-top: 1px solid var(--gray-100);">
                            <td style="padding: 0.5rem 0.625rem; font-family: ui-monospace, monospace;">
                                {{ $baris['merge'] === '' ? '— (tanpa merge)' : $baris['merge'] }}
                            </td>
                            <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                {{ number_format($baris['poy'], 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                {{ number_format($baris['dty'], 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                {{ $baris['rendemen'] === null ? '-' : number_format($baris['rendemen'], 2, ',', '.').'%' }}
                            </td>
                            <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                {{ $baris['waste'] === null ? '-' : number_format($baris['waste'], 2, ',', '.').'%' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 1.5rem; text-align: center; color: var(--gray-500);">
                                Belum ada data pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="background: var(--gray-50); font-weight: 600;">
                    <tr>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">Total</td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ number_format($hasil['totalPoy'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ number_format($hasil['totalDty'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, ',', '.').'%' }}
                        </td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, ',', '.').'%' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="margin-top: 1rem; border: 1px solid var(--gray-200); background: var(--gray-50); color: var(--gray-600); border-radius: 0.5rem; padding: 0.75rem; font-size: 0.75rem; line-height: 1.5;">
            {{ $disclaimer }}
        </div>
    </x-filament::section>
</x-filament-panels::page>
