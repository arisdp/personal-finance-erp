@extends('adminlte::page')

@section('title', 'Investasi & Portfolio')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Manajemen Investasi</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('investments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Asset
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Modal (Cost)</span>
                    <span class="info-box-number">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Nilai Pasar (Market)</span>
                    <span class="info-box-number">Rp {{ number_format($totalMarketValue, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box {{ $totalGainLoss >= 0 ? 'bg-success' : 'bg-danger' }}">
                <span class="info-box-icon"><i class="fas {{ $totalGainLoss >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Unrealized Profit/Loss</span>
                    <span class="info-box-number">Rp {{ number_format($totalGainLoss, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box {{ $gainLossPercentage >= 0 ? 'bg-olive' : 'bg-maroon' }}">
                <span class="info-box-icon"><i class="fas fa-percent"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ROI %</span>
                    <span class="info-box-number">{{ number_format($gainLossPercentage, 2) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title">Portofolio Aset Saat Ini</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Asset / Ticker</th>
                        <th>Type</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Avg Price</th>
                        <th class="text-right">Market Price</th>
                        <th class="text-right">Gain/Loss</th>
                        <th class="text-center" width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holdings as $holding)
                    <tr>
                        <td>
                            <span class="font-weight-bold">{{ $holding->asset_name }}</span>
                            @if($holding->ticker) <span class="badge badge-secondary ml-1">{{ $holding->ticker }}</span> @endif
                        </td>
                        <td><span class="badge badge-info">{{ ucfirst($holding->asset_type) }}</span></td>
                        <td class="text-right">{{ (float)$holding->quantity }}</td>
                        <td class="text-right text-muted">Rp {{ number_format($holding->avg_buy_price, 0, ',', '.') }}</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($holding->current_price, 0, ',', '.') }}</td>
                        <td class="text-right {{ $holding->is_profit ? 'text-success' : 'text-danger' }}">
                            {{ $holding->is_profit ? '+' : '' }}{{ number_format($holding->gain_loss_percentage, 2) }}%<br>
                            <small>(Rp {{ number_format($holding->unrealized_gain_loss, 0, ',', '.') }})</small>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('investments.updatePrice', $holding) }}" method="POST" class="form-inline d-inline mr-1">
                                @csrf @method('PATCH')
                                <div class="input-group input-group-sm">
                                    <input type="number" name="new_price" class="form-control" placeholder="Update Price" style="width: 100px;">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-sync-alt"></i></button>
                                    </div>
                                </div>
                            </form>
                            <form action="{{ route('investments.destroy', $holding) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus aset ini?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">Portofolio kosong. Silakan tambahkan aset baru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
