@extends('adminlte::page')

@section('title', 'Proyeksi Kekayaan Masa Depan')

@section('content_header')
    <h1><i class="fas fa-magic mr-2"></i> Analisa & Proyeksi Masa Depan</h1>
@stop

@section('content')
<div class="row">
    <!-- Simulator Controls -->
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Simulator Proyeksi</h3>
            </div>
            <form action="{{ route('reports.projections') }}" method="GET">
                <div class="card-body">
                    <div class="form-group">
                        <label for="annual_return">Imbal Hasil Investasi (% per tahun)</label>
                        <div class="input-group">
                            <input type="number" name="annual_return" id="annual_return" class="form-control" value="{{ $annualReturn }}" step="0.1" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="text-muted">Estimasi pertumbuhan aset Anda tiap tahun.</small>
                    </div>

                    <div class="form-group">
                        <label for="annual_inflation">Kenaikan Biaya Hidup (% per tahun)</label>
                        <div class="input-group">
                            <input type="number" name="annual_inflation" id="annual_inflation" class="form-control" value="{{ $annualInflation }}" step="0.1" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="text-muted">Estimasi kenaikan pengeluaran akibat inflasi.</small>
                    </div>

                    <div class="form-group">
                        <label for="years">Jangka Waktu Proyeksi (Tahun)</label>
                        <select name="years" id="years" class="form-control">
                            <option value="5" {{ $years == 5 ? 'selected' : '' }}>5 Tahun</option>
                            <option value="10" {{ $years == 10 ? 'selected' : '' }}>10 Tahun</option>
                            <option value="15" {{ $years == 15 ? 'selected' : '' }}>15 Tahun</option>
                            <option value="20" {{ $years == 20 ? 'selected' : '' }}>20 Tahun</option>
                        </select>
                    </div>

                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle mr-1"></i> Sistem menghitung berdasarkan rata-rata tabungan bulanan (Pemasukan - Pengeluaran) dari riwayat transaksi Anda.
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sync-alt mr-1"></i> Update Proyeksi
                    </button>
                </div>
            </form>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td class="pl-3 font-weight-bold">Net Worth Saat Ini</td>
                            <td class="text-right pr-3 text-primary font-weight-bold">Rp {{ number_format($currentNetWorth, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="pl-3">Target Net Worth ({{ $years }}th)</td>
                            <td class="text-right pr-3 font-weight-bold text-success">Rp {{ number_format(end($projections)['net_worth'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart & Data -->
    <div class="col-md-8">
        <div class="card card-outline card-success shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Estimasi Pertumbuhan Kekayaan</h3>
            </div>
            <div class="card-body">
                <div style="height: 400px;">
                    <canvas id="projectionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">Snapshot Tahunan</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Tanggal</th>
                                <th class="text-right">Estimasi Net Worth</th>
                                <th class="text-right">Kenaikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $prevNw = $currentNetWorth; @endphp
                            @foreach($projections as $p)
                                <tr>
                                    <td>{{ $p['period'] }}</td>
                                    <td>{{ $p['date'] }}</td>
                                    <td class="text-right font-weight-bold text-success">Rp {{ number_format($p['net_worth'], 0, ',', '.') }}</td>
                                    <td class="text-right">
                                        @if($p['period'] !== 'Current')
                                            <span class="text-success">
                                                <i class="fas fa-arrow-up mr-1 text-xs"></i>
                                                {{ number_format((($p['net_worth'] - $prevNw) / ($prevNw ?: 1)) * 100, 1) }}%
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @php $prevNw = $p['net_worth']; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('projectionChart').getContext('2d');
    const dataLabels = {!! json_encode(array_column($projections, 'period')) !!};
    const dataValues = {!! json_encode(array_column($projections, 'net_worth')) !!};

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(40, 167, 69, 0.5)');
    gradient.addColorStop(1, 'rgba(40, 167, 69, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dataLabels,
            datasets: [{
                label: 'Estimasi Net Worth (Rp)',
                data: dataValues,
                borderColor: '#28a745',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toLocaleString() + ' jt';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Net Worth: Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
@stop
