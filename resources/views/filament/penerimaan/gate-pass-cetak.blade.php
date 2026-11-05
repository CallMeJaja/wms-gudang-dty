@php
    /** @var \App\Models\TransaksiMasuk $record */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gate Pass {{ $record->nomor_gate_pass ?? $record->nomor_transaksi }}</title>
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
        .tujuan { margin-bottom: 1rem; font-size: 11px; font-weight: 600; }
        .tujuan div { line-height: 1.5; }
        .judul { text-align: center; margin-bottom: 1rem; }
        .judul h1 { margin: 0; font-size: 18px; letter-spacing: 0.12em; }
        .judul .nomor { margin-top: 0.25rem; font-family: ui-monospace, "Courier New", monospace; font-weight: 700; }
        .judul .tanggal { margin-top: 0.125rem; color: #4b5563; }
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
        <div class="tujuan">
            <div>To Security IPCI,</div>
            <div>Dept : STORE</div>
        </div>

        <div class="judul">
            <h1>GATE PASS</h1>
            <div class="nomor">No. {{ $record->nomor_gate_pass ?? '— (DRAF — belum diterbitkan)' }}</div>
            <div class="tanggal">Tanggal: {{ $record->tanggal?->format('d-m-Y') }}</div>
        </div>

        <table class="rincian">
            <thead>
                <tr>
                    <th style="width: 2.5rem;">NO</th>
                    <th>Material Code</th>
                    <th>Material Description</th>
                    <th>UoM</th>
                    <th class="num" style="width: 6rem;">Qty</th>
                    <th>Batch</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->detail as $i => $detail)
                    @php
                        $batch = trim((string) $detail->batch);
                        if ($batch === '' || $batch === '-') {
                            $legacy = trim((string) $detail->merge);
                            $batch = ($legacy !== '' && $legacy !== '-')
                                ? $legacy
                                : (string) \App\Models\TransaksiMasukDetail::batchDariKeterangan($detail->keterangan);
                        }
                        $batch = trim($batch);
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="mono">{{ $detail->barang?->kode_barang }}</td>
                        <td>{{ $detail->barang?->nama_barang }}</td>
                        <td class="center">{{ $detail->barang?->satuanDasar?->kode ?? $detail->satuanKemasan?->kode }}</td>
                        <td class="num">{{ number_format((float) $detail->berat_kg, 2, ',', '.') }}</td>
                        <td class="mono">{{ $batch !== '' ? $batch : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="catatan">
            <strong>REMARKS:</strong> {{ $record->nomor_transaksi }}@if ($record->catatan) — {{ $record->catatan }}@endif
        </div>

        <div class="tanda-tangan">
            <div>
                <div>Prepared,</div>
                <div class="ruang"></div>
                <div class="nama">( ................ )</div>
            </div>
            <div>
                <div>Approved,</div>
                <div class="ruang"></div>
                <div class="nama">( ................ )</div>
            </div>
            <div>
                <div>Received,</div>
                <div class="ruang"></div>
                <div class="nama">( ................ )</div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
