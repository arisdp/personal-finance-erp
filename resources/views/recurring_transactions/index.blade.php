@extends('adminlte::page')

@section('title', 'Recurring Transactions')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Transaksi Berulang & Tagihan</h1>
        <a href="{{ route('recurring.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Jadwal Baru
        </a>
    </div>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Status</th>
                        <th>Nama Tagihan</th>
                        <th>Frekuensi</th>
                        <th class="text-right">Nominal</th>
                        <th>Jatuh Tempo Berikutnya</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recurringTransactions as $recurring)
                    <tr>
                        <td class="text-center">
                            @if($recurring->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $recurring->name }}</strong><br>
                            <small class="text-muted">{{ $recurring->debitAccount->name }} &rarr; {{ $recurring->creditAccount->name }}</small>
                        </td>
                        <td><span class="badge badge-info">{{ ucfirst($recurring->frequency) }}</span></td>
                        <td class="text-right font-weight-bold">
                            Rp {{ number_format($recurring->amount, 0, ',', '.') }}
                        </td>
                        <td>
                            @php 
                                $diff = now()->diffInDays($recurring->next_due_date, false);
                                $color = $diff < 3 ? 'text-danger font-weight-bold' : ($diff < 7 ? 'text-warning' : 'text-dark');
                            @endphp
                            <span class="{{ $color }}">
                                {{ $recurring->next_due_date->format('d M Y') }}
                                @if($diff < 0)
                                    <small class="badge badge-danger ml-1">Terlambat!</small>
                                @elseif($diff <= 3)
                                    <small class="badge badge-warning ml-1">Segera!</small>
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('recurring.edit', $recurring) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('recurring.destroy', $recurring) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus jadwal ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-alt fa-3x mb-3 d-block"></i>
                            Belum ada transaksi berulang yang dijadwalkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
