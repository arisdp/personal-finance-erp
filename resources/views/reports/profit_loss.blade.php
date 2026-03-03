@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi (Profit & Loss)')

@section('content_header')
    <h1>Laporan Laba Rugi</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Periode</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.pl') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Mulai:</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">Sampai:</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Laba Rugi ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})</h3>
                    <div class="card-tools">
                        <a href="{{ route('reports.pl.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger btn-sm mr-2">
                            <i class="fas fa-file-pdf mr-1"></i> Download PDF
                        </a>
                        <button type="button" class="btn btn-tool" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th colspan="2">PENDAPATAN (INCOME)</th>
                                <th class="text-right">NOMINAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomeAccounts as $income)
                            <tr>
                                <td width="10%"><code>{{ $income['code'] }}</code></td>
                                <td>{{ $income['name'] }}</td>
                                <td class="text-right">Rp {{ number_format($income['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-muted text-center italic small">Belum ada pendapatan terinput</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light font-weight-bold border-bottom">
                            <tr>
                                <td colspan="2" class="text-right">TOTAL PENDAPATAN</td>
                                <td class="text-right text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>

                        <thead class="bg-light">
                            <tr>
                                <th colspan="2" class="pt-4">PENGELUARAN (EXPENSES)</th>
                                <th class="text-right pt-4">NOMINAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenseAccounts as $expense)
                            <tr>
                                <td><code>{{ $expense['code'] }}</code></td>
                                <td>{{ $expense['name'] }}</td>
                                <td class="text-right">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-muted text-center italic small">Belum ada pengeluaran terinput</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="2" class="text-right">TOTAL PENGELUARAN</td>
                                <td class="text-right text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>

                        <tfoot class="bg-dark text-white font-weight-bold" style="font-size: 1.25rem;">
                            <tr>
                                <td colspan="2" class="text-right py-3">LABA (RUGI) BERSIH</td>
                                <td class="text-right py-3">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
