<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keluar-Masuk</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .periode { margin-bottom: 10px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        .num { text-align: right; }
        tfoot td { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Keluar-Masuk</h1>
    <div class="periode">Periode: {{ $filter['dari'] }} s.d. {{ $filter['sampai'] }}</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th><th>Barang</th><th>Merge</th><th>Grade</th><th>Jenis</th><th>Pos</th><th>Ref</th><th>Masuk (Kg)</th><th>Keluar (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris as $mutasi)
                <tr>
                    <td>{{ $mutasi->tanggal?->format('Y-m-d') }}</td>
                    <td>{{ $mutasi->barang?->kode_barang }}</td>
                    <td>{{ $mutasi->merge }}</td>
                    <td>{{ $mutasi->grade?->kode }}</td>
                    <td>{{ $mutasi->jenis_transaksi instanceof \BackedEnum ? $mutasi->jenis_transaksi->value : $mutasi->jenis_transaksi }}</td>
                    <td>{{ $mutasi->pos instanceof \BackedEnum ? $mutasi->pos->value : $mutasi->pos }}</td>
                    <td>{{ $mutasi->ref_nomor }}</td>
                    <td class="num">{{ number_format((float) $mutasi->masuk_qty, 2, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $mutasi->keluar_qty, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">Total</td>
                <td class="num">{{ number_format($total['masuk'], 2, '.', '') }}</td>
                <td class="num">{{ number_format($total['keluar'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
