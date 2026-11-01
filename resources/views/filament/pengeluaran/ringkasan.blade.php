@php
    /** @var \App\Models\TransaksiKeluar $transaksi */
    $totalNetto = (float) $transaksi->detail->sum(fn (\App\Models\TransaksiKeluarDetail $d) => (float) $d->berat_netto_kg);
    $totalGross = (float) $transaksi->detail->sum(fn (\App\Models\TransaksiKeluarDetail $d) => (float) $d->berat_gross_kg);

    $warnaStatus = match ($transaksi->status) {
        \App\Enums\StatusDokumen::Draft => ['bg' => 'var(--gray-100)', 'text' => 'var(--gray-700)'],
        \App\Enums\StatusDokumen::Posted, \App\Enums\StatusDokumen::Issued => ['bg' => 'var(--success-50)', 'text' => 'var(--success-700)'],
        \App\Enums\StatusDokumen::Void => ['bg' => 'var(--danger-50)', 'text' => 'var(--danger-700)'],
        \App\Enums\StatusDokumen::Cancelled => ['bg' => 'var(--warning-50)', 'text' => 'var(--warning-700)'],
        default => ['bg' => 'var(--gray-100)', 'text' => 'var(--gray-700)'],
    };

    $info = array_filter([
        'Tanggal' => $transaksi->tanggal?->format('d-m-Y'),
        'Jenis' => $transaksi->jenis_keluar?->label(),
        'Partner' => $transaksi->partner?->nama,
        'No. Surat Jalan' => $transaksi->nomor_surat_jalan,
        'No. Kendaraan' => $transaksi->no_kendaraan,
        'Sopir' => $transaksi->nama_sopir,
        'PIC Pengirim' => $transaksi->pic_pengirim,
        'PIC Mengetahui' => $transaksi->pic_mengetahui,
        'Shift' => $transaksi->shift ? 'Shift '.$transaksi->shift : null,
        'Operator' => $transaksi->operator_nama,
        'Dibuat oleh' => trim(($transaksi->pembuat?->name ?? '—').' · '.($transaksi->created_at?->format('d-m-Y H:i') ?? '')),
    ], fn ($nilai) => $nilai !== null && $nilai !== '');
@endphp

<div style="font-size: 0.875rem; color: var(--gray-950);">
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        <strong style="font-family: ui-monospace, monospace;">{{ $transaksi->nomor_transaksi }}</strong>
        <span style="display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 600; background: {{ $warnaStatus['bg'] }}; color: {{ $warnaStatus['text'] }};">
            {{ $transaksi->status->label() }}
        </span>
        <span style="color: var(--gray-500);">{{ $transaksi->jenis_keluar?->label() }}</span>
        <span style="margin-left: auto; font-weight: 600;">
            {{ number_format($totalNetto, 2, ',', '.') }} kg netto
            @if ($totalGross > 0)
                · {{ number_format($totalGross, 2, ',', '.') }} kg gross
            @endif
        </span>
    </div>

    @if ($transaksi->status === \App\Enums\StatusDokumen::Void)
        <div style="border: 1px solid var(--danger-200); background: var(--danger-50); color: var(--danger-700); border-radius: 0.5rem; padding: 0.75rem; margin-bottom: 1rem;">
            <div style="font-weight: 600;">Transaksi dibatalkan (void)</div>
            <div style="font-size: 0.8rem; margin-top: 0.25rem;">Alasan: {{ $transaksi->alasan_void ?? '—' }}</div>
            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.25rem;">
                {{ $transaksi->voided_at?->format('d-m-Y H:i') }} oleh {{ $transaksi->pemvoid?->name ?? '—' }}
            </div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem 1.5rem; margin-bottom: 1.25rem;">
        @foreach ($info as $label => $nilai)
            <div style="display: flex; gap: 0.75rem; border-bottom: 1px solid var(--gray-100); padding-bottom: 0.375rem;">
                <div style="width: 9rem; flex: none; color: var(--gray-500);">{{ $label }}</div>
                <div style="font-weight: 500;">{{ $nilai }}</div>
            </div>
        @endforeach
    </div>

    <div style="overflow-x: auto; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
            <thead style="background: var(--gray-50); color: var(--gray-500); text-align: left;">
                <tr>
                    <th style="padding: 0.5rem 0.625rem;">#</th>
                    <th style="padding: 0.5rem 0.625rem;">Barang</th>
                    <th style="padding: 0.5rem 0.625rem;">Merge</th>
                    <th style="padding: 0.5rem 0.625rem;">Grade</th>
                    <th style="padding: 0.5rem 0.625rem; text-align: right;">Kemasan</th>
                    <th style="padding: 0.5rem 0.625rem; text-align: right;">Bobbin</th>
                    <th style="padding: 0.5rem 0.625rem; text-align: right;">Netto (KG)</th>
                    <th style="padding: 0.5rem 0.625rem; text-align: right;">Gross (KG)</th>
                    <th style="padding: 0.5rem 0.625rem;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksi->detail as $i => $detail)
                    <tr style="border-top: 1px solid var(--gray-100);">
                        <td style="padding: 0.5rem 0.625rem; color: var(--gray-500);">{{ $i + 1 }}</td>
                        <td style="padding: 0.5rem 0.625rem;">
                            <div style="font-family: ui-monospace, monospace;">{{ $detail->barang?->kode_barang }}</div>
                            <div style="font-size: 0.75rem; color: var(--gray-500);">{{ $detail->barang?->nama_barang }}</div>
                        </td>
                        <td style="padding: 0.5rem 0.625rem; font-family: ui-monospace, monospace;">{{ $detail->merge ?: '—' }}</td>
                        <td style="padding: 0.5rem 0.625rem;">{{ $detail->grade?->kode ?? '—' }}</td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            @if ($detail->jumlah_kemasan !== null)
                                {{ number_format((float) $detail->jumlah_kemasan, 2, ',', '.') }}
                                <span style="color: var(--gray-500);">{{ $detail->satuanKemasan?->kode }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">{{ $detail->jumlah_bobbin ?? '—' }}</td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right; font-weight: 600;">
                            {{ number_format((float) $detail->berat_netto_kg, 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.5rem 0.625rem; text-align: right;">
                            {{ $detail->berat_gross_kg !== null ? number_format((float) $detail->berat_gross_kg, 2, ',', '.') : '—' }}
                        </td>
                        <td style="padding: 0.5rem 0.625rem;">{{ $detail->keterangan ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="background: var(--gray-50);">
                <tr>
                    <th colspan="6" style="padding: 0.5rem 0.625rem; text-align: right;">Total</th>
                    <th style="padding: 0.5rem 0.625rem; text-align: right;">{{ number_format($totalNetto, 2, ',', '.') }}</th>
                    <th style="padding: 0.5rem 0.625rem; text-align: right;">{{ number_format($totalGross, 2, ',', '.') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($transaksi->catatan)
        <div style="margin-top: 1rem; border-top: 1px solid var(--gray-200); padding-top: 0.75rem;">
            <div style="color: var(--gray-500); font-size: 0.75rem;">Catatan</div>
            <div>{{ $transaksi->catatan }}</div>
        </div>
    @endif
</div>
