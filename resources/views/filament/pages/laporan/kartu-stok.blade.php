<x-filament-panels::page>
    <p style="font-size: 0.8rem; color: var(--gray-500); margin: 0;">
        Saldo awal + saldo berjalan per baris, beserta kolom pos.
    </p>

    <x-filament::section>
        <x-slot name="heading">Filter</x-slot>

        <form method="GET" action="{{ url()->current() }}">
            <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Barang</span>
                    <x-filament::input.wrapper style="min-width: 14rem;">
                        <x-filament::input.select name="barang_id">
                            <option value="">— Pilih barang —</option>
                            @foreach ($daftarBarang as $item)
                                <option value="{{ $item->id }}" @selected((int) $filter['barang_id'] === (int) $item->id)>
                                    {{ $item->kode_barang }} — {{ $item->nama_barang }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Merge</span>
                    <x-filament::input.wrapper style="min-width: 9rem;">
                        <x-filament::input type="text" name="merge" value="{{ $filter['merge'] }}" />
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Grade</span>
                    <x-filament::input.wrapper style="min-width: 9rem;">
                        <x-filament::input.select name="grade_id">
                            <option value="">— Semua —</option>
                            @foreach ($daftarGrade as $grade)
                                <option value="{{ $grade->id }}" @selected((int) $filter['grade_id'] === (int) $grade->id)>
                                    {{ $grade->kode }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--gray-500);">Pos</span>
                    <x-filament::input.wrapper style="min-width: 9rem;">
                        <x-filament::input.select name="pos">
                            <option value="">— Semua —</option>
                            <option value="AKTIF" @selected($filter['pos'] === 'AKTIF')>Aktif</option>
                            <option value="BUFFER" @selected($filter['pos'] === 'BUFFER')>Buffer</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

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
                    <x-filament::button type="submit">Tampilkan</x-filament::button>
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

    <x-filament::section>
        @if (! $lengkap)
            <p style="font-size: 0.85rem; color: var(--gray-500); margin: 0;">
                Pilih barang dan isi merge untuk menampilkan kartu stok.
            </p>
        @else
            <div style="border: 1px solid var(--info-200); background: var(--info-50); color: var(--info-700); border-radius: 0.5rem; padding: 0.625rem 0.75rem; font-size: 0.8rem; margin-bottom: 0.75rem;">
                Saldo awal per {{ $filter['dari'] }}:
                <strong>{{ number_format($saldoAwal, 2, ',', '.') }} kg</strong>
            </div>

            <div style="overflow-x: auto; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                    <thead style="background: var(--gray-50); color: var(--gray-500); text-align: left;">
                        <tr>
                            <th style="padding: 0.5rem 0.625rem;">Tanggal</th>
                            <th style="padding: 0.5rem 0.625rem;">Ref / Keterangan</th>
                            <th style="padding: 0.5rem 0.625rem;">Jenis</th>
                            <th style="padding: 0.5rem 0.625rem;">Pos</th>
                            <th style="padding: 0.5rem 0.625rem; text-align: right;">Masuk (Kg)</th>
                            <th style="padding: 0.5rem 0.625rem; text-align: right;">Keluar (Kg)</th>
                            <th style="padding: 0.5rem 0.625rem; text-align: right;">Saldo (Kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($baris as $mutasi)
                            <tr style="border-top: 1px solid var(--gray-100);">
                                <td style="padding: 0.5rem 0.625rem;">{{ $mutasi->tanggal?->format('d-m-Y') }}</td>
                                <td style="padding: 0.5rem 0.625rem; font-family: ui-monospace, monospace;">{{ $mutasi->ref_nomor }}</td>
                                <td style="padding: 0.5rem 0.625rem;">{{ $mutasi->jenis_transaksi->label() }}</td>
                                <td style="padding: 0.5rem 0.625rem;">
                                    <span style="display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.5rem; font-size: 0.7rem; font-weight: 600; background: {{ $mutasi->pos->value === 'BUFFER' ? 'var(--warning-50)' : 'var(--success-50)' }}; color: {{ $mutasi->pos->value === 'BUFFER' ? 'var(--warning-700)' : 'var(--success-700)' }};">
                                        {{ $mutasi->pos->value }}
                                    </span>
                                </td>
                                <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                    {{ number_format((float) $mutasi->masuk_qty, 2, ',', '.') }}
                                </td>
                                <td style="padding: 0.5rem 0.625rem; text-align: right;">
                                    {{ number_format((float) $mutasi->keluar_qty, 2, ',', '.') }}
                                </td>
                                <td style="padding: 0.5rem 0.625rem; text-align: right; font-weight: 600;">
                                    {{ number_format((float) $mutasi->getAttribute('saldo_berjalan'), 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 1.5rem; text-align: center; color: var(--gray-500);">
                                    Tidak ada mutasi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
