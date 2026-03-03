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

@stop