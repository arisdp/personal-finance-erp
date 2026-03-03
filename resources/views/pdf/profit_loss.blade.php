<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 8px; text-align: left; }
        .bg-light { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; font-size: 10px; color: #999; }
        .border-top { border-top: 1px solid #333; }
        .border-double { border-top: 3px double #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN LABA RUGI</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</p>
    </div>

    <table style="margin-bottom: 20px;">
        <thead>
            <tr class="bg-light">
                <th colspan="2" class="font-bold">PENDAPATAN (INCOME)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incomeAccounts as $income)
            <tr>
                <td width="70%">{{ $income['name'] }}</td>
                <td width="30%" class="text-right">{{ number_format($income['amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="font-bold border-top">
                <td>TOTAL PENDAPATAN</td>
                <td class="text-right">{{ number_format($totalIncome, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr class="bg-light">
                <th colspan="2" class="font-bold">PENGELUARAN (EXPENSES)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenseAccounts as $expense)
            <tr>
                <td width="70%">{{ $expense['name'] }}</td>
                <td width="30%" class="text-right">{{ number_format($expense['amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="font-bold border-top">
                <td>TOTAL PENGELUARAN</td>
                <td class="text-right">({{ number_format($totalExpense, 0, ',', '.') }})</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 20px;">
        <tr class="font-bold bg-light" style="font-size: 14px;">
            <td width="70%">LABA / (RUGI) BERSIH</td>
            <td width="30%" class="text-right {{ $netProfit < 0 ? 'text-danger' : '' }}">
                {{ number_format($netProfit, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
