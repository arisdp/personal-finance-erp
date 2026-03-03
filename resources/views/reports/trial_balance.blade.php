@extends('adminlte::page')

@section('title', 'Neraca Saldo (Trial Balance)')

@section('content_header')
    <h1>Neraca Saldo</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Tanggal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.trial') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Per Tanggal:</label>
                    <input type="date" name="date" class="form-control" value="{{ $asOfDate }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
            </form>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Laporan Neraca Saldo per {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</h3>
            <div class="card-tools">
                <a href="{{ route('reports.trial.pdf', ['date' => $asOfDate]) }}" class="btn btn-danger btn-sm mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-tool" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th width="15%">Kode Akun</th>
                        <th width="45%">Nama Akun</th>
                        <th width="20%" class="text-right">Debit</th>
                        <th width="20%" class="text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td><code>{{ $row['code'] }}</code></td>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-right">
                            {{ $row['debit'] > 0 ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ $row['credit'] > 0 ? 'Rp ' . number_format($row['credit'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">Tidak ada data transaksi hingga tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light font-weight-bold" style="font-size: 1.1rem;">
                    <tr>
                        <td colspan="2" class="text-right">TOTAL</td>
                        <td class="text-right text-primary">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="text-right text-primary">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if(abs($totalDebit - $totalCredit) > 0.01)
        <div class="card-footer bg-danger text-white text-center">
            <i class="fas fa-exclamation-triangle"></i> PERINGATAN: Neraca tidak seimbang! Selisih: Rp {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}
        </div>
        @endif
    </div>
@stop
