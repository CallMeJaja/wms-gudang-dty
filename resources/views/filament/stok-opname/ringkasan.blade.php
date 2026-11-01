@php
    /** @var \App\Models\StokOpname $opname */
    $selisih = (float) $opname->selisih_qty;
    $warnaSelisih = match (true) {
        $selisih > 0 => ['bg' => 'var(--success-50)', 'text' => 'var(--success-700)'],
        $selisih < 0 => ['bg' => 'var(--danger-50)', 'text' => 'var(--danger-700)'],
        default => ['bg' => 'var(--gray-100)', 'text' => 'var(--gray-700)'],
    };

    $penyesuaian = null;
    if ($opname->transaksi_masuk_id !== null) {
        $penyesuaian = 'IN '.($opname->transaksiMasuk?->nomor_transaksi ?? '—');
    } elseif ($opname->transaksi_keluar_id !== null) {
        $penyesuaian = 'OUT '.($opname->transaksiKeluar?->nomor_transaksi ?? '—');
    }

    $info = array_filter([
        'Tanggal' => $opname->tanggal?->format('d-m-Y'),
        'Barang' => trim(($opname->barang?->kode_barang ?? '—').' — '.($opname->barang?->nama_barang ?? '')),
        'Kategori' => $opname->barang?->kategori?->kode,
        'Merge' => $opname->merge,
        'Grade' => $opname->grade?->kode,
        'Stok sistem' => number_format((float) $opname->stok_sistem_qty, 2, ',', '.').' kg',
        'Stok fisik' => number_format((float) $opname->stok_fisik_qty, 2, ',', '.').' kg',
        'Penyesuaian' => $penyesuaian,
        'Dibuat oleh' => trim(($opname->pembuat?->name ?? '—').' · '.($opname->created_at?->format('d-m-Y H:i') ?? '')),
        'Keterangan' => $opname->keterangan,
    ], fn ($nilai) => $nilai !== null && $nilai !== '');
@endphp

<div style="font-size: 0.875rem; color: var(--gray-950);">
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        <strong style="font-family: ui-monospace, monospace;">{{ $opname->nomor_opname }}</strong>
        <span style="color: var(--gray-500);">
            {{ $opname->barang?->kode_barang }} · {{ $opname->merge }} · {{ $opname->grade?->kode }}
        </span>
        <span style="margin-left: auto; display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 600; background: {{ $warnaSelisih['bg'] }}; color: {{ $warnaSelisih['text'] }};">
            Selisih {{ number_format($selisih, 2, ',', '.') }} kg
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem 1.5rem; margin-bottom: 1rem;">
        @foreach ($info as $label => $nilai)
            <div style="display: flex; gap: 0.75rem; border-bottom: 1px solid var(--gray-100); padding-bottom: 0.375rem;">
                <div style="width: 9rem; flex: none; color: var(--gray-500);">{{ $label }}</div>
                <div style="font-weight: 500;">{{ $nilai }}</div>
            </div>
        @endforeach
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem;">
        <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
            <div style="font-size: 0.75rem; color: var(--gray-500);">Stok sistem</div>
            <div style="font-weight: 600;">{{ number_format((float) $opname->stok_sistem_qty, 2, ',', '.') }} kg</div>
        </div>
        <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.75rem;">
            <div style="font-size: 0.75rem; color: var(--gray-500);">Stok fisik</div>
            <div style="font-weight: 600;">{{ number_format((float) $opname->stok_fisik_qty, 2, ',', '.') }} kg</div>
        </div>
        <div style="border: 1px solid {{ $selisih === 0.0 ? 'var(--gray-200)' : ($selisih > 0 ? 'var(--success-200)' : 'var(--danger-200)') }}; border-radius: 0.5rem; padding: 0.75rem; background: {{ $warnaSelisih['bg'] }};">
            <div style="font-size: 0.75rem; color: var(--gray-500);">Selisih</div>
            <div style="font-weight: 600; color: {{ $warnaSelisih['text'] }};">
                {{ $selisih > 0 ? '+' : '' }}{{ number_format($selisih, 2, ',', '.') }} kg
            </div>
        </div>
    </div>

    @if ($opname->keterangan)
        <div style="margin-top: 1rem; border-top: 1px solid var(--gray-200); padding-top: 0.75rem;">
            <div style="color: var(--gray-500); font-size: 0.75rem;">Keterangan</div>
            <div>{{ $opname->keterangan }}</div>
        </div>
    @endif
</div>
