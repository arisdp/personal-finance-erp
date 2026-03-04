<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Neraca Saldo - {{ $asOfDate }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN NERACA SALDO</h1>
        <p>Per Tanggal: {{ date('d/m/Y', strtotime($asOfDate)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Kode</th>
                <th width="45%">Nama Akun</th>
                <th width="20%" class="text-right">Debit</th>
                <th width="20%" class="text-right">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr style="{{ !$row['is_postable'] ? 'font-weight: bold; background-color: #f2f2f2;' : '' }}">
                <td style="padding-left: {{ 8 + ($row['level'] * 15) }}px;"><code>{{ $row['code'] }}</code></td>
                <td style="padding-left: {{ 8 + ($row['level'] * 15) }}px;">{{ $row['name'] }}</td>
                <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f9f9f9;">
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalCredit, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="text-center" style="margin-top: 20px;">
        @if(round($totalDebit) == round($totalCredit))
            <p style="color: green; font-weight: bold;">STATUS: SEIMBANG (BALANCED)</p>
        @else
            <p style="color: red; font-weight: bold;">STATUS: TIDAK SEIMBANG (UNBALANCED)</p>
        @endif
    </div>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
