<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Besar - {{ $account->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN BUKU BESAR</h1>
        <p>{{ $account->code }} - {{ $account->name }}</p>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Tanggal</th>
                <th width="45%">Keterangan</th>
                <th width="12%" class="text-right">Debit</th>
                <th width="12%" class="text-right">Kredit</th>
                <th width="16%" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" class="font-bold">SALDO AWAL</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-bold">{{ number_format($beginningBalance, 0, ',', '.') }}</td>
            </tr>
            @php 
                $currentBalance = $beginningBalance;
                $isDebitNormal = in_array($account->category, ['asset', 'expense']);
            @endphp
            @foreach($mutations as $mutation)
                @php
                    if ($isDebitNormal) {
                        $currentBalance += ($mutation->debit - $mutation->credit);
                    } else {
                        $currentBalance += ($mutation->credit - $mutation->debit);
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ date('d/m/Y', strtotime($mutation->journalEntry->date)) }}</td>
                    <td>{{ $mutation->journalEntry->description }}</td>
                    <td class="text-right">{{ $mutation->debit > 0 ? number_format($mutation->debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $mutation->credit > 0 ? number_format($mutation->credit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($currentBalance, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f9f9f9;">
                <td colspan="2" class="text-right">TOTAL MUTASI</td>
                <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($currentBalance, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
