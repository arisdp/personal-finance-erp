@extends('adminlte::page')

@section('title', 'Dashboard Keuangan')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Dashboard Keluarga</h1>
        
        @if(session('active_workspace_id'))
            @php $activeWorkspace = \App\Models\Workspace::find(session('active_workspace_id'))->name; @endphp
            <div>
                <span class="badge badge-info py-2 px-3 text-md">
                    <i class="fas fa-building mr-1"></i> {{ $activeWorkspace }}
                </span>
            </div>
        @else
            <div>
                <span class="badge badge-danger py-2 px-3 text-md">
                    <i class="fas fa-exclamation-triangle"></i> Workspace belum diset
                </span>
            </div>
        @endif
    </div>
@stop

@section('content')

    <!-- Barisan ke-1: Key Financial Metrics -->
    <div class="row">

        <!-- Net Worth -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>Rp {{ number_format($netWorth, 0, ',', '.') }}</h3>
                    <p>Total Net Worth</p>
                </div>
                <div class="icon">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <!-- a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a-->
            </div>
        </div>

        <!-- Total Cash -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Rp {{ number_format($totalCash, 0, ',', '.') }}</h3>
                    <p>Total Kas & Bank</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- Total Investment -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Rp {{ number_format($totalInvestment, 0, ',', '.') }}</h3>
                    <p>Total Aset Investasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- Total Debt -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>Rp {{ number_format($totalDebt, 0, ',', '.') }}</h3>
                    <p>Total Hutang / Kewajiban</p>
                </div>
                <div class="icon">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Barisan ke-2: Cashflow & Dana Darurat -->
    <div class="row">

        <!-- Cashflow Bulan Ini -->
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Cashflow Bulan Ini</h3>
                        <a href="{{ route('journals.index') }}">Lihat Jurnal</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-3 pb-3">
                        <p class="text-success text-xl mb-0">
                            <i class="fas fa-arrow-up mr-2"></i> Rp {{ number_format($cashflowThisMonth['income'], 0, ',', '.') }}
                        </p>
                        <p class="d-flex flex-column text-right">
                            <span class="text-muted">Pemasukan</span>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom mb-3 pb-3">
                        <p class="text-danger text-xl mb-0">
                            <i class="fas fa-arrow-down mr-2"></i> Rp {{ number_format($cashflowThisMonth['expense'], 0, ',', '.') }}
                        </p>
                        <p class="d-flex flex-column text-right">
                            <span class="text-muted">Pengeluaran</span>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <p class="{{ $cashflowThisMonth['net'] >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold text-xl mb-0">
                            <i class="fas fa-equals mr-2"></i> Rp {{ number_format($cashflowThisMonth['net'], 0, ',', '.') }}
                        </p>
                        <p class="d-flex flex-column text-right">
                            <span class="text-muted">Nett Cashflow</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dana Darurat Progress -->
        <div class="col-md-6">
            <div class="card card-outline card-success h-100">
                <div class="card-header">
                    <h3 class="card-title">Status Dana Darurat</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            @php
                                $colorClass = 'text-danger';
                                if($emergencyFund['status'] === 'warning') $colorClass = 'text-warning';
                                if($emergencyFund['status'] === 'healthy') $colorClass = 'text-success';
                            @endphp
                            
                            <h2 class="{{ $colorClass }} font-weight-bold display-4">{{ $emergencyFund['current_months'] }} <small class="text-muted text-lg">Bulan</small></h2>
                            <p class="text-muted">Target Anda: {{ $emergencyFund['target_months'] }} Bulan Pengeluaran</p>
                        </div>
                    </div>

                    <p class="mb-1 d-flex justify-content-between">
                        <span>Persentase Kesiapan</span>
                        <span class="font-weight-bold">{{ $emergencyFund['progress_percent'] }}%</span>
                    </p>
                    <div class="progress progress-sm mb-4">
                        @php
                            $bgClass = 'bg-danger';
                            if($emergencyFund['status'] === 'warning') $bgClass = 'bg-warning';
                            if($emergencyFund['status'] === 'healthy') $bgClass = 'bg-success';
                        @endphp
                        <div class="progress-bar {{ $bgClass }}" style="width: {{ $emergencyFund['progress_percent'] }}%"></div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <span class="text-muted d-block">Terkumpul</span>
                            <span class="font-weight-bold">Rp {{ number_format($emergencyFund['total_fund'], 0, ',', '.') }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Target Nominal</span>
                            <span class="font-weight-bold">Rp {{ number_format($emergencyFund['target_amount'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

    </div>

    <!-- Barisan ke-3: Credit Card & Installment Summary -->
    <div class="row mt-4">
        <div class="col-md-7">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-credit-card mr-1"></i> Penggunaan Kartu Kredit & Paylater</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-3">Akun</th>
                                    <th class="text-right">Used</th>
                                    <th class="text-right">Limit</th>
                                    <th width="25%">Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($creditCards as $cc)
                                <tr>
                                    <td class="pl-3">{{ $cc['name'] }}</td>
                                    <td class="text-right font-weight-bold text-danger">Rp {{ number_format($cc['used'], 0, ',', '.') }}</td>
                                    <td class="text-right text-muted">Rp {{ number_format($cc['limit'], 0, ',', '.') }}</td>
                                    <td>
                                        <div class="progress progress-xs mt-2" title="{{ $cc['usage_percent'] }}%">
                                            <div class="progress-bar bg-danger" style="width: {{ $cc['usage_percent'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted italic small">Data kartu kredit tidak ditemukan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-outline card-purple">
                <div class="card-header">
                    <h3 class="card-title text-purple"><i class="fas fa-file-contract mr-1"></i> Ringkasan Cicilan (Hutang)</h3>
                    <div class="card-tools">
                        <a href="{{ route('installments.index') }}" class="btn btn-tool btn-sm"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 text-center border-bottom bg-light">
                        <small class="text-uppercase text-muted d-block mb-1">Total Cicilan Bulanan</small>
                        <h3 class="font-weight-bold mb-0 text-purple">Rp {{ number_format($installmentSummary['total_monthly'], 0, ',', '.') }}</h3>
                        <small class="text-muted">{{ $installmentSummary['count'] }} Cicilan Aktif</small>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Sisa Hutang:</span>
                            <span class="font-weight-bold text-danger">Rp {{ number_format($installmentSummary['total_remaining'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Barisan ke-4: Active Bills Status (User Request) -->
    @if(count($upcomingBills) > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title text-warning"><i class="fas fa-bell mr-1"></i> Status Tagihan Bulan Ini</h3>
                    <div class="card-tools">
                        <a href="{{ route('recurring.index') }}" class="btn btn-tool btn-sm"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Tagihan</th>
                                    <th>Jatuh Tempo</th>
                                    <th class="text-right">Nominal</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingBills as $bill)
                                <tr>
                                    <td class="pl-3">{{ $bill['name'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill['next_due_date'])->format('d M Y') }}</td>
                                    <td class="text-right font-weight-bold">Rp {{ number_format($bill['amount'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @php 
                                            $diff = now()->diffInDays($bill['next_due_date'], false);
                                            $isPaidThisMonth = false;
                                            if (isset($bill['last_posted_date']) && $bill['last_posted_date']) {
                                                $lastPaid = \Carbon\Carbon::parse($bill['last_posted_date']);
                                                if ($bill['frequency'] === 'monthly') {
                                                    $isPaidThisMonth = $lastPaid->month == now()->month && $lastPaid->year == now()->year;
                                                } elseif ($bill['frequency'] === 'weekly') {
                                                    $isPaidThisMonth = $lastPaid->diffInWeeks(now()) === 0;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($isPaidThisMonth)
                                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Lunas</span>
                                        @else
                                            @if($diff < 0)
                                                <span class="badge badge-danger">Terlambat {{ abs($diff) }} Hari</span>
                                            @elseif($diff == 0)
                                                <span class="badge badge-warning">Hari Ini!</span>
                                            @else
                                                <span class="badge badge-info">{{ $diff }} Hari Lagi</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Barisan ke-5: Budget Monitoring (User Request) -->
    <div class="row mt-4 mb-5">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bullseye mr-1"></i> Monitoring Anggaran & Alarm Limit</h3>
                    <div class="card-tools">
                        <a href="{{ route('budgets.index') }}" class="btn btn-tool btn-sm">
                            <i class="fas fa-cog"></i> Atur Budget
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Kategori Pengeluaran</th>
                                    <th class="text-right">Target Budget</th>
                                    <th class="text-right">Realisasi (Actual)</th>
                                    <th class="text-right">Sisa / Lebih</th>
                                    <th width="30%">Usage Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetSummary as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="text-right text-muted">Rp {{ number_format($item['budget'], 0, ',', '.') }}</td>
                                    <td class="text-right font-weight-bold">Rp {{ number_format($item['actual'], 0, ',', '.') }}</td>
                                    <td class="text-right {{ $item['remaining'] < 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                        {{ $item['remaining'] < 0 ? '-' : '' }}Rp {{ number_format(abs($item['remaining']), 0, ',', '.') }}
                                        @if($item['remaining'] < 0)
                                            <i class="fas fa-exclamation-circle ml-1" title="Melebihi Budget!"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress progress-sm mt-2" title="{{ $item['percent'] }}% Terpakai">
                                            @php 
                                                $barClass = 'bg-info'; 
                                                if($item['percent'] > 80) $barClass = 'bg-warning';
                                                if($item['percent'] > 100) $barClass = 'bg-danger';
                                            @endphp
                                            <div class="progress-bar {{ $barClass }}" style="width: {{ min(100, $item['percent']) }}%"></div>
                                        </div>
                                        <small class="{{ $item['percent'] > 100 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                            {{ $item['percent'] }}% dari budget
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Belum ada budget yang diatur untuk bulan ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop