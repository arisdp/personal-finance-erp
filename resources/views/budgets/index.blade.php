@extends('adminlte::page')

@section('title', 'Budget Management')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Anggaran Bulanan (Budgets)</h1>
        <a href="{{ route('budgets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Buat Budget Baru
        </a>
    </div>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Periode</th>
                    <th>Akun Pengeluaran</th>
                    <th class="text-right">Alokasi Anggaran</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $budget)
                <tr>
                    <td>
                        <span class="badge badge-info">
                            {{ date('F', mktime(0, 0, 0, $budget->month, 1)) }} {{ $budget->year }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $budget->account->name }}</strong><br>
                        <small class="text-muted">{{ $budget->account->code }}</small>
                    </td>
                    <td class="text-right font-weight-bold">
                        Rp {{ number_format($budget->amount, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus budget ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted"> Belum ada anggaran yang diatur. Klik "Buat Budget Baru" untuk memulai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
