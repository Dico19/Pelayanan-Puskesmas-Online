<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Antrian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2,h3 { margin: 0 0 8px 0; }
        .muted { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #222; padding: 6px; }
        th { background: #f2f2f2; }
        .mb { margin-bottom: 14px; }
    </style>
</head>
<body>
    <h2>Rekap Antrian</h2>
    <div class="muted">
        Periode: <b>{{ $from }}</b> s/d <b>{{ $to }}</b> |
        Poli: <b>{{ $poli ? strtoupper($poli) : 'SEMUA' }}</b>
    </div>

    <div class="mb">
        <h3>Ringkasan</h3>
        <table>
            <tr><th>Total</th><th>Sudah Dipanggil</th><th>Belum Dipanggil</th></tr>
            <tr>
                <td style="text-align:center">{{ $summary['total'] }}</td>
                <td style="text-align:center">{{ $summary['sudah'] }}</td>
                <td style="text-align:center">{{ $summary['belum'] }}</td>
            </tr>
        </table>
    </div>

    <div class="mb">
        <h3>Rekap Per Hari</h3>
        <table>
            <tr><th>Tanggal</th><th>Total</th><th>Sudah</th><th>Belum</th></tr>
            @foreach($perHari as $r)
                <tr>
                    <td>{{ $r->tanggal }}</td>
                    <td style="text-align:center">{{ (int)$r->total }}</td>
                    <td style="text-align:center">{{ (int)$r->sudah_dipanggil }}</td>
                    <td style="text-align:center">{{ (int)$r->belum_dipanggil }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mb">
        <h3>Rekap Per Poli</h3>
        <table>
            <tr><th>Poli</th><th>Total</th><th>Sudah</th><th>Belum</th></tr>
            @foreach($perPoli as $r)
                <tr>
                    <td>{{ strtoupper((string)$r->poli) }}</td>
                    <td style="text-align:center">{{ (int)$r->total }}</td>
                    <td style="text-align:center">{{ (int)$r->sudah_dipanggil }}</td>
                    <td style="text-align:center">{{ (int)$r->belum_dipanggil }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
