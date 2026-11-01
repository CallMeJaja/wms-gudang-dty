<x-filament-panels::page>
    <p style="font-size: 0.8rem; color: var(--gray-500); margin: 0;">
        Sumber: mutasi stok (hanya transaksi posted/issued).
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

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Kategori</span>
                    <x-filament::input.wrapper style="min-width: 9rem;">
                        <x-filament::input.select name="kategori">
                            <option value="">— Semua —</option>
                            @foreach ($daftarKategori as $kategori)
                                <option value="{{ $kategori->kode }}" @selected($filter['kategori'] === $kategori->kode)>
                                    {{ $kategori->kode }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Jenis Transaksi</span>
                    <x-filament::input.wrapper style="min-width: 9rem;">
                        <x-filament::input.select name="arus">
                            <option value="">— In/Out —</option>
                            <option value="MASUK" @selected($filter['arus'] === 'MASUK')>Masuk</option>
                            <option value="KELUAR" @selected($filter['arus'] === 'KELUAR')>Keluar</option>
                            <option value="VOID" @selected($filter['arus'] === 'VOID')>Void</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Merge</span>
                    <x-filament::input.wrapper style="min-width: 9rem;">
                        <x-filament::input type="text" name="merge" value="{{ $filter['merge'] }}" />
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
        heading="Data Mutasi Stok"
        description="Rincian mutasi masuk dan keluar pada periode terpilih."
        icon="heroicon-o-table-cells"
    >
        <div style="overflow-x: auto; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
            <table class="wms-report-table" style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                <thead style="background: var(--gray-50); color: var(--gray-500); text-align: left;">
                    <tr>
                        <th style="padding: 0.5rem 0.625rem;">Tanggal</th>
                        <th style="padding: 0.5rem 0.625rem;">Barang</th>
                        <th style="padding: 0.5rem 0.625rem;">Merge</th>
                        <th style="padding: 0.5rem 0.625rem;">Grade</th>
                        <th style="padding: 0.5rem 0.625rem;">Jenis</th>
                        <th style="padding: 0.5rem 0.625rem;">Pos</th>
                        <th style="padding: 0.5rem 0.625rem;">Ref Nomor</th>
                        <th style="padding: 0.5rem 0.625rem; text-align: right;">Masuk (Kg)</th>
                        <th style="padding: 0.5rem 0.625rem; text-align: right;">Keluar (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($baris as $mutasi)
                        <tr style="border-top: 1px solid var(--gray-100);">
                            <td style="padding: 0.5rem 0.625rem;">{{ $mutasi->tanggal?->format('d-m-Y') }}</td>
                            <td style="padding: 0.5rem 0.625rem; font-family: ui-monospace, monospace;">
                                {{ $mutasi->barang?->kode_barang }}
                            </td>
                            <td style="padding: 0.5rem 0.625rem; font-family: ui-monospace, monospace;">{{ $mutasi->merge }}</td>
                            <td style="padding: 0.5rem 0.625rem;">{{ $mutasi->grade?->kode }}</td>
                            <td style="padding: 0.5rem 0.625rem;">{{ $mutasi->jenis_transaksi->label() }}</td>
                            <td style="padding: 0.5rem 0.625rem;">
                                <span style="display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.5rem; font-size: 0.7rem; font-weight: 600; background: {{ $mutasi->pos->value === 'BUFFER' ? 'var(--warning-50)' : 'var(--success-50)' }}; color: {{ $mutasi->pos->value === 'BUFFER' ? 'var(--warning-700)' : 'var(--success-700)' }};">
                                    {{ $mutasi->pos->value }}
                                </span>
                            </td>
                            <td style="padding: 0.5rem 0.625rem; font-family: ui-monospace, monospace;">{{ $mutasi->ref_nomor }}</td>
                            <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                {{ number_format((float) $mutasi->masuk_qty, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                {{ number_format((float) $mutasi->keluar_qty, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 1.5rem; text-align: center; color: var(--gray-500);">
                                Belum ada mutasi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="background: var(--gray-50); font-weight: 600;">
                    <tr>
                        <td colspan="7" style="padding: 0.5rem 0.625rem; text-align: right;">Total</td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ number_format($total['masuk'], 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ number_format($total['keluar'], 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
