@extends('adminlte::page')

@section('title', 'Buku Besar (Ledger)')

@section('content_header')
    <h1>Laporan Buku Besar</h1>
@stop

@section('content')
    <!-- Filter Panel -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('ledger.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Pilih Akun</label>
                            <select name="account_id" class="form-control select2">
                                <option value="">-- Pilih Akun --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $selectedAccount && $selectedAccount->id == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }} 
                                        ({{ strtoupper($account->category) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Hingga Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedAccount)
        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-light">
                    <span class="info-box-icon"><i class="fas fa-hourglass-start text-secondary"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saldo Awal (per {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }})</span>
                        <span class="info-box-number text-lg">Rp {{ number_format($beginningBalance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-box bg-light">
                    <span class="info-box-icon"><i class="fas fa-exchange-alt text-info"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mutasi Peridoe Ini</span>
                        <small class="text-success"><i class="fas fa-plus"></i> Debet: Rp {{ number_format($totalDebit, 0, ',', '.') }}</small><br>
                        <small class="text-danger"><i class="fas fa-minus"></i> Kredit: Rp {{ number_format($totalCredit, 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>

            @php
                // Hitung Saldo Akhir Berdasarkan Kategori
                // Asset/Expense = Debit bertambah, Kredit Berkurang
                // Liability/Income/Equity = Kredit bertambah, Debit Berkurang
                $isDebitNormal = in_array($selectedAccount->category, ['asset', 'expense']);
                
                if($isDebitNormal) {
                    $endingBalance = $beginningBalance + $totalDebit - $totalCredit;
                } else {
                    $endingBalance = $beginningBalance + $totalCredit - $totalDebit;
                }
            @endphp

            <div class="col-md-4">
                <div class="info-box {{ $endingBalance >= 0 ? 'bg-success' : 'bg-danger' }}">
                    <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saldo Akhir (per {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})</span>
                        <span class="info-box-number text-xl">Rp {{ number_format($endingBalance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="card card-outline card-success">
            <div class="card-header border-0">
                <h3 class="card-title">Rincian Transaksi: <strong>{{ $selectedAccount->name }}</strong></h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="12%">Tanggal</th>
                            <th width="15%">Referensi</th>
                            <th width="28%">Uraian Keterangan</th>
                            <th width="15%" class="text-right">Debet</th>
                            <th width="15%" class="text-right">Kredit</th>
                            <th width="15%" class="text-right">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Berisikan Saldo Awal -->
                        <tr class="bg-light font-weight-bold">
                            <td colspan="3" class="text-right">SALDO AWAL</td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">Rp {{ number_format($beginningBalance, 0, ',', '.') }}</td>
                        </tr>

                        @php $runningBalance = $beginningBalance; @endphp

                        @forelse($mutations as $mutation)
                            @php
                                if($isDebitNormal) {
                                    $runningBalance += $mutation->debit;
                                    $runningBalance -= $mutation->credit;
                                } else {
                                    $runningBalance += $mutation->credit;
                                    $runningBalance -= $mutation->debit;
                                }
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($mutation->journalEntry->date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('journals.show', $mutation->journalEntry->id) }}">{{ $mutation->journalEntry->reference }}</a>
                                </td>
                                <td>{{ $mutation->description ?: $mutation->journalEntry->description }}</td>
                                <td class="text-right">{{ $mutation->debit > 0 ? number_format($mutation->debit, 0, ',', '.') : '-' }}</td>
                                <td class="text-right">{{ $mutation->credit > 0 ? number_format($mutation->credit, 0, ',', '.') : '-' }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($runningBalance, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        @endforelse

                        <!-- Total Mutation -->
                        <tr class="bg-light font-weight-bold">
                            <td colspan="3" class="text-right">TOTAL MUTASI</td>
                            <td class="text-right text-success">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                            <td class="text-right text-danger">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($endingBalance, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info"></i> Silakan Pilih Akun</h5>
            Gunakan filter di atas untuk memilih akun dan rentang tanggal yang ingin Anda tampilkan Buku Besarnya.
        </div>
    @endif
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: "-- Pilih Akun --",
            allowClear: true
        });
    });
</script>
@stop
