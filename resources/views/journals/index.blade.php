@extends('adminlte::page')

@section('title', 'Jurnal Transaksi')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Buku Jurnal Umum</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('journals.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Catat Transaksi
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Riwayat Transaksi Terakhir</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="15%">Tanggal</th>
                        <th width="15%">Referensi</th>
                        <th width="35%">Keterangan</th>
                        <th width="15%">Nominal Transaksi</th>
                        <th width="10%">Pembuat</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        @php
                            // Total debit represents the total amount of the transaction
                            $amount = $journal->lines->sum('debit'); 
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($journal->date)->format('d M Y') }}</td>
                            <td><span class="text-muted">{{ $journal->reference }}</span></td>
                            <td>{{ $journal->description }}</td>
                            <td class="font-weight-bold">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                            <td><span class="badge badge-secondary">{{ $journal->creator->name ?? 'System' }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('journals.show', $journal) }}" class="btn btn-xs btn-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('journals.edit', $journal) }}" class="btn btn-xs btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('journals.destroy', $journal) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Hapus transaksi ini?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Belum ada catatan jurnal di workspace ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($journals->hasPages())
            <div class="card-footer">
                {{ $journals->links() }}
            </div>
        @endif
    </div>
@stop