@extends('adminlte::page')

@section('title', 'Finance Dashboard')

@section('content_header')
<h1>Finance Dashboard</h1>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($assets,2) }}</h3>
                <p>Total Assets</p>
            </div>
            <div class="icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($liabilities,2) }}</h3>
                <p>Total Liabilities</p>
            </div>
            <div class="icon"><i class="fas fa-credit-card"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($equity,2) }}</h3>
                <p>Total Equity</p>
            </div>
            <div class="icon"><i class="fas fa-university"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($netIncome,2) }}</h3>
                <p>Net Income</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>

</div>

@stop