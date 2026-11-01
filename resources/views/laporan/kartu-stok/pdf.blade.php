<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Stok</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .info { margin-bottom: 10px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h1>Kartu Stok</h1>
    <div class="info">Periode: {{ $dari }} s.d. {{ $sampai }} — Saldo awal: {{ number_format($saldoAwal, 2, '.', '') }} kg</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th><th>Ref</th><th>Pos</th><th>Masuk (Kg)</th><th>Keluar (Kg)</th><th>Saldo (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris as $mutasi)
                <tr>
                    <td>{{ $mutasi->tanggal?->format('Y-m-d') }}</td>
                    <td>{{ $mutasi->ref_nomor }}</td>
                    <td>{{ $mutasi->pos instanceof \BackedEnum ? $mutasi->pos->value : $mutasi->pos }}</td>
                    <td class="num">{{ number_format((float) $mutasi->masuk_qty, 2, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $mutasi->keluar_qty, 2, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $mutasi->getAttribute('saldo_berjalan'), 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
