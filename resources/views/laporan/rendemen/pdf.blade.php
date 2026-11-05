<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rendemen POY Masuk vs DTY Keluar</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .periode { margin-bottom: 10px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #555; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        .num { text-align: right; }
        .surplus { margin-top: 10px; padding: 6px; border: 1px solid #b91c1c; color: #7f1d1d; }
        .disclaimer { margin-top: 10px; font-style: italic; color: #444; }
    </style>
</head>
<body>
    <h1>Rendemen POY Masuk vs DTY Keluar</h1>
    <div class="periode">Periode: {{ $dari }} s.d. {{ $sampai }}</div>
    <table>
        <thead>
            <tr>
                <th>Metrik</th><th class="num">Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>POY Masuk (kg)</td><td class="num">{{ number_format($hasil['totalPoy'], 2, '.', '') }}</td></tr>
            <tr><td>POY Masuk (ton)</td><td class="num">{{ number_format($hasil['totalPoy'] / 1000, 2, '.', '') }}</td></tr>
            <tr><td>DTY Keluar (kg)</td><td class="num">{{ number_format($hasil['totalDty'], 2, '.', '') }}</td></tr>
            <tr><td>DTY Keluar (ton)</td><td class="num">{{ number_format($hasil['totalDty'] / 1000, 2, '.', '') }}</td></tr>
            <tr><td>Rendemen (%)</td><td class="num">{{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, '.', '') }}</td></tr>
            <tr><td>Waste / Selisih (kg)</td><td class="num">{{ number_format($hasil['wasteKg'], 2, '.', '') }}</td></tr>
            <tr><td>Waste / Selisih (ton)</td><td class="num">{{ number_format($hasil['wasteKg'] / 1000, 2, '.', '') }}</td></tr>
            <tr><td>Waste (%)</td><td class="num">{{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, '.', '') }}</td></tr>
            <tr><td>Target Bulanan (kg)</td><td class="num">{{ number_format($hasil['targetKg'], 2, '.', '') }}</td></tr>
            <tr><td>Target Bulanan (ton)</td><td class="num">{{ number_format($hasil['targetKg'] / 1000, 2, '.', '') }}</td></tr>
            <tr><td>Pencapaian Target (%)</td><td class="num">{{ $hasil['pencapaianTarget'] === null ? '-' : number_format($hasil['pencapaianTarget'], 2, '.', '') }}</td></tr>
            <tr><td>Selisih Target (kg)</td><td class="num">{{ number_format($hasil['selisihTargetKg'], 2, '.', '') }}</td></tr>
        </tbody>
    </table>

    @if ($hasil['surplus'])
        <div class="surplus">DTY keluar lebih besar dari POY masuk. Data surplus/tidak valid — periksa transaksi pada periode ini.</div>
    @endif

    <div class="disclaimer">{{ $disclaimer }}</div>
</body>
</html>
