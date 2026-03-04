@extends('adminlte::page')

@section('title', 'Installments Management')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Manajemen Cicilan & Hutang</h1>
        <a href="{{ route('installments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Cicilan Baru
        </a>
    </div>
@stop

@section('content')
<div class="row">
    @forelse($installments as $installment)
    <div class="col-md-6 col-lg-4">
        <div class="card card-outline {{ $installment->is_completed ? 'card-success' : 'card-primary' }}">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">{{ $installment->name }}</h3>
                <div class="card-tools">
                    <span class="badge {{ $installment->is_completed ? 'badge-success' : 'badge-info' }}">
                        {{ strtoupper($installment->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="mb-0">Rp {{ number_format($installment->monthly_amount, 0, ',', '.') }}</h2>
                    <p class="text-muted">Per Bulan</p>
                </div>

                <div class="progress progress-sm mb-2" title="{{ $installment->progress_percentage }}% Selesai">
                    <div class="progress-bar bg-success" style="width: {{ $installment->progress_percentage }}%"></div>
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span>Tenor: {{ $installment->paid_periods }}/{{ $installment->total_periods }} Bulan</span>
                    <span class="font-weight-bold text-success">{{ $installment->progress_percentage }}%</span>
                </div>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>Total Pinjaman</b> <a class="float-right text-dark font-weight-bold">Rp {{ number_format($installment->total_amount, 0, ',', '.') }}</a>
                    </li>
                    <li class="list-group-item border-bottom-0 pb-0">
                        <b>Sisa Hutang</b> <a class="float-right text-danger font-weight-bold">Rp {{ number_format($installment->remaining_amount, 0, ',', '.') }}</a>
                    </li>
                    <li class="list-group-item border-0 pt-1">
                        <small class="text-muted">Jatuh tempo akhir: {{ $installment->end_date ? $installment->end_date->format('d M Y') : '-' }}</small>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="{{ route('installments.edit', $installment) }}" class="btn btn-link btn-sm text-info"><i class="fas fa-edit mr-1"></i> Edit</a>
                <form action="{{ route('installments.destroy', $installment) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-link btn-sm text-danger" onclick="return confirm('Hapus data cicilan ini?')"><i class="fas fa-trash mr-1"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-light text-center py-5 border">
            <i class="fas fa-credit-card fa-3x mb-3 text-muted"></i>
            <h5 class="text-muted">Belum ada data cicilan yang terdaftar.</h5>
            <p class="text-muted">Klik tombol "Tambah Cicilan Baru" untuk melacak pinjaman atau cicilan barang Anda.</p>
        </div>
    </div>
    @endforelse
</div>
@stop
