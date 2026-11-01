@php
    /** @var \App\Models\TransaksiKeluar $record */
    $totalNetto = (float) $record->detail->sum(fn (\App\Models\TransaksiKeluarDetail $d) => (float) $d->berat_netto_kg);
    $totalGross = (float) $record->detail->sum(fn (\App\Models\TransaksiKeluarDetail $d) => (float) $d->berat_gross_kg);
    $totalBobbin = (int) $record->detail->sum('jumlah_bobbin');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan {{ $record->nomor_surat_jalan ?? $record->nomor_transaksi }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 1.5rem;
            font-family: "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            background: #f3f4f6;
        }
        .toolbar { max-width: 210mm; margin: 0 auto 1rem; display: flex; justify-content: flex-end; gap: 0.5rem; }
        .toolbar button {
            border: 0; border-radius: 0.375rem; padding: 0.5rem 1rem;
            background: #1d4ed8; color: #fff; font-size: 12px; cursor: pointer;
        }
        .page { max-width: 210mm; margin: 0 auto; background: #fff; padding: 14mm 12mm; }
        .judul { text-align: center; margin-bottom: 1rem; }
        .judul h1 { margin: 0; font-size: 18px; letter-spacing: 0.12em; }
        .judul .nomor { margin-top: 0.25rem; font-family: ui-monospace, "Courier New", monospace; font-weight: 700; }
        .judul .tanggal { margin-top: 0.125rem; color: #4b5563; }
        .info { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1rem; }
        .info table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .info th { width: 8.5rem; text-align: left; font-weight: 600; padding: 0.125rem 0.5rem 0.125rem 0; white-space: nowrap; }
        .info td { padding: 0.125rem 0; white-space: nowrap; }
        table.rincian { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.rincian th, table.rincian td { border: 1px solid #9ca3af; padding: 0.35rem 0.45rem; }
        table.rincian thead th {
            background: #f3f4f6;
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        table.rincian td.num, table.rincian th.num { text-align: right; }
        table.rincian td.center, table.rincian th.center { text-align: center; }
        table.rincian tfoot td { background: #f9fafb; font-weight: 700; }
        .mono { font-family: ui-monospace, "Courier New", monospace; }
        .catatan { margin-top: 0.75rem; }
        .tanda-tangan { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3rem; text-align: center; }
        .tanda-tangan .ruang { height: 4rem; }
        .tanda-tangan .nama { white-space: nowrap; font-size: 11px; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .page { max-width: none; padding: 0; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="cetak-root">
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak</button>
    </div>

    <div class="page">
        <div class="judul">
            <h1>SURAT JALAN</h1>
            <div class="nomor">No. {{ $record->nomor_surat_jalan ?? '—' }}</div>
            <div class="tanggal">Tanggal: {{ $record->tanggal?->format('d-m-Y') }}</div>
        </div>

        <div class="info">
            <table>
                <tr><th>Nama Langganan</th><td>: {{ $record->partner?->nama ?? '—' }}</td></tr>
                <tr><th>No. Kendaraan</th><td>: {{ $record->no_kendaraan ?? '—' }}</td></tr>
                <tr><th>Sopir</th><td>: {{ $record->nama_sopir ?? '—' }}</td></tr>
            </table>
            <table>
                <tr><th>Pengirim</th><td>: {{ $record->pic_pengirim ?? '—' }}</td></tr>
                <tr><th>Mengetahui</th><td>: {{ $record->pic_mengetahui ?? '—' }}</td></tr>
                <tr><th>No. Transaksi</th><td>: {{ $record->nomor_transaksi }}</td></tr>
            </table>
        </div>

        <table class="rincian">
            <thead>
                <tr>
                    <th style="width: 2.5rem;">No</th>
                    <th>Item Code</th>
                    <th>Merge</th>
                    <th>Grade</th>
                    <th class="num">Packing</th>
                    <th class="num">Units (Bobbin)</th>
                    <th class="num">Netto (Kg)</th>
                    <th class="num">Gross (Kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->detail as $i => $detail)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="mono">{{ $detail->barang?->kode_barang }}</td>
                        <td class="mono">{{ $detail->merge }}</td>
                        <td class="center">{{ $detail->grade?->kode }}</td>
                        <td class="num">
                            {{ $detail->jumlah_kemasan !== null ? number_format((float) $detail->jumlah_kemasan, 0, ',', '.') : '—' }}
                            {{ $detail->satuanKemasan?->kode ? ' '.$detail->satuanKemasan->kode : '' }}
                        </td>
                        <td class="num">{{ $detail->jumlah_bobbin !== null ? number_format((int) $detail->jumlah_bobbin, 0, ',', '.') : '—' }}</td>
                        <td class="num">{{ number_format((float) $detail->berat_netto_kg, 2, ',', '.') }}</td>
                        <td class="num">{{ $detail->berat_gross_kg !== null ? number_format((float) $detail->berat_gross_kg, 2, ',', '.') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="num">Total</td>
                    <td class="num">{{ number_format($totalBobbin, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($totalNetto, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($totalGross, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($record->catatan)
            <div class="catatan">
                <strong>Catatan:</strong> {{ $record->catatan }}
            </div>
        @endif

        <div class="tanda-tangan">
            <div>
                <div>Penerima,</div>
                <div class="ruang"></div>
                <div class="nama">( ........................ )</div>
            </div>
            <div>
                <div>Pengirim,</div>
                <div class="ruang"></div>
                <div class="nama">( {{ $record->pic_pengirim ?? '................' }} )</div>
            </div>
            <div>
                <div>Mengetahui,</div>
                <div class="ruang"></div>
                <div class="nama">( {{ $record->pic_mengetahui ?? '................' }} )</div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
