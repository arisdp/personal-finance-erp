@extends('adminlte::page')

@section('title', 'Instrumen Investasi')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-bar text-info mr-2"></i>Master Instrumen Investasi</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('investments.index') }}" class="btn btn-secondary">
                <i class="fas fa-briefcase mr-1"></i> Portofolio Holding
            </a>
        </div>
    </div>
@stop

@section('content')

    {{-- Add Instrument Form --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Tambah Instrumen / Emiten Baru</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('investment_instruments.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ticker / Kode <span class="text-danger">*</span></label>
                            <input type="text" name="ticker"
                                class="form-control text-uppercase @error('ticker') is-invalid @enderror" placeholder="BBCA"
                                value="{{ old('ticker') }}" required>
                            @error('ticker')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Nama Instrumen <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Bank Central Asia Tbk" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tipe Aset <span class="text-danger">*</span></label>
                            <select name="asset_type" class="form-control" required>
                                <option value="stock">📈 Saham</option>
                                <option value="gold">🥇 Emas</option>
                                <option value="crypto">💎 Crypto</option>
                                <option value="mutual_fund">📦 Reksadana</option>
                                <option value="other">📋 Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Harga Awal (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="current_price"
                                class="form-control @error('current_price') is-invalid @enderror" placeholder="10000"
                                step="1" min="0" value="{{ old('current_price') }}" required>
                            @error('current_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Opsional"
                                value="{{ old('notes') }}">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Instruments Table --}}
    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Instrumen</h3>
            <div class="card-tools">
                <small class="text-muted">Update harga 1x → otomatis berlaku untuk semua holding instrumen tersebut</small>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Ticker</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th class="text-right">Harga Saat Ini</th>
                        <th class="text-center">Holding</th>
                        <th class="text-right">Total Modal</th>
                        <th class="text-right">Nilai Pasar</th>
                        <th class="text-right">Unrealized P/L</th>
                        <th class="text-center" width="200">Update Harga</th>
                        <th class="text-center" width="60">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instruments as $instrument)
                        @php
                            $cost = $instrument->total_cost_basis;
                            $market = $instrument->total_market_value;
                            $gainLoss = $instrument->total_gain_loss;
                            $isProfit = $gainLoss >= 0;
                            $pct = $instrument->gain_loss_percentage;
                        @endphp
                        <tr>
                            <td><code class="font-weight-bold">{{ $instrument->ticker }}</code></td>
                            <td>
                                {{ $instrument->name }}
                                @if ($instrument->notes)
                                    <br><small class="text-muted">{{ $instrument->notes }}</small>
                                @endif
                            </td>
                            <td>
                                @php $types=['stock'=>'📈 Saham','gold'=>'🥇 Emas','crypto'=>'💎 Crypto','mutual_fund'=>'📦 Reksadana','other'=>'📋 Lainnya']; @endphp
                                <span
                                    class="badge badge-info">{{ $types[$instrument->asset_type] ?? $instrument->asset_type }}</span>
                            </td>
                            <td class="text-right font-weight-bold">
                                Rp {{ number_format($instrument->current_price, 0, ',', '.') }}
                                @if ($instrument->last_price_update)
                                    <br><small
                                        class="text-muted">{{ $instrument->last_price_update->format('d M Y H:i') }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $instrument->holdings->count() }} lot</span>
                            </td>
                            <td class="text-right text-muted">Rp {{ number_format($cost, 0, ',', '.') }}</td>
                            <td class="text-right font-weight-bold">Rp {{ number_format($market, 0, ',', '.') }}</td>
                            <td class="text-right {{ $isProfit ? 'text-success' : 'text-danger' }} font-weight-bold">
                                {{ $isProfit ? '+' : '' }}{{ number_format($pct, 2) }}%
                                <br><small>Rp {{ number_format($gainLoss, 0, ',', '.') }}</small>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('investment_instruments.updatePrice', $instrument) }}"
                                    method="POST" class="form-inline d-inline">
                                    @csrf @method('PATCH')
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="new_price" class="form-control"
                                            placeholder="Harga baru" step="1" min="0"
                                            style="width: 110px;">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-sync-alt"></i> Update
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('investment_instruments.destroy', $instrument) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus instrumen {{ $instrument->ticker }}? Holdings yang terkait TIDAK akan terhapus.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-3 d-block"></i>
                                Belum ada instrumen. Tambahkan di form di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('css')
    <style>
        code {
            font-size: 1rem;
            color: #e83e8c;
        }

        table td {
            vertical-align: middle !important;
        }
    </style>
@stop
