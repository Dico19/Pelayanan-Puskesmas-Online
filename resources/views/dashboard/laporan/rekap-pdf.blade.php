<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Antrian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f2f2f2; }
        .mb { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h3 class="mb">Rekap Antrian</h3>
    <div class="mb">Periode: <b>{{ $from }}</b> s/d <b>{{ $to }}</b></div>
    <div class="mb">Tipe: <b>{{ $tipe === 'per_poli' ? 'Per Poli' : 'Harian' }}</b></div>
    <div class="mb">Filter Poli: <b>{{ $poli ?: 'Semua Poli' }}</b></div>

    <div class="mb">
        Ringkasan:
        Total <b>{{ $summary['total'] }}</b> |
        Sudah <b>{{ $summary['sudah'] }}</b> |
        Belum <b>{{ $summary['belum'] }}</b>
    </div>

    <table>
        <thead>
        <tr>
            @if($tipe === 'per_poli')
                <th>Poli</th><th>Total</th><th>Sudah</th><th>Belum</th>
            @else
                <th>Tanggal</th><th>Total</th><th>Sudah</th><th>Belum</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                @if($tipe === 'per_poli')
                    <td>{{ strtoupper($r->poli) }}</td>
                    <td>{{ $r->total }}</td>
                    <td>{{ $r->sudah }}</td>
                    <td>{{ $r->belum }}</td>
                @else
                    <td>{{ $r->tanggal }}</td>
                    <td>{{ $r->total }}</td>
                    <td>{{ $r->sudah }}</td>
                    <td>{{ $r->belum }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
