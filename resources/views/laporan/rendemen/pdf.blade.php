<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rendemen POY vs DTY</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .periode { margin-bottom: 10px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #555; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        .num { text-align: right; }
        tfoot td { font-weight: bold; }
        .disclaimer { margin-top: 10px; font-style: italic; color: #444; }
    </style>
</head>
<body>
    <h1>Rekonsiliasi POY vs DTY</h1>
    <div class="periode">Periode: {{ $dari }} s.d. {{ $sampai }}</div>
    <table>
        <thead>
            <tr>
                <th>Merge POY Asal</th><th>POY Keluar (Kg)</th><th>DTY Masuk (Kg)</th><th>Rendemen (%)</th><th>Waste (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hasil['perMerge'] as $r)
                <tr>
                    <td>{{ $r['merge'] }}</td>
                    <td class="num">{{ number_format($r['poy'], 2, '.', '') }}</td>
                    <td class="num">{{ number_format($r['dty'], 2, '.', '') }}</td>
                    <td class="num">{{ $r['rendemen'] === null ? '-' : number_format($r['rendemen'], 2, '.', '') }}</td>
                    <td class="num">{{ $r['waste'] === null ? '-' : number_format($r['waste'], 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="num">{{ number_format($hasil['totalPoy'], 2, '.', '') }}</td>
                <td class="num">{{ number_format($hasil['totalDty'], 2, '.', '') }}</td>
                <td class="num">{{ $hasil['rendemen'] === null ? '-' : number_format($hasil['rendemen'], 2, '.', '') }}</td>
                <td class="num">{{ $hasil['waste'] === null ? '-' : number_format($hasil['waste'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
    <div class="disclaimer">{{ $disclaimer }}</div>
</body>
</html>
