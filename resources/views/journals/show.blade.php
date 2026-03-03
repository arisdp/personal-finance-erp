@extends('adminlte::page')

@section('title', 'Detail Transaksi - ' . $journal->reference)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detail Jurnal: {{ $journal->reference }}</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('journals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('journals.edit', $journal) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informasi Transaksi</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ \Carbon\Carbon::parse($journal->date)->format('d M Y') }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="20%">Akun</th>
                                <th width="40%">Keterangan Baris</th>
                                <th class="text-right" width="20%">Debit</th>
                                <th class="text-right" width="20%">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journal->lines as $line)
                            <tr>
                                <td>
                                    <span class="font-weight-bold">{{ $line->account->code }}</span><br>
                                    <small>{{ $line->account->name }}</small>
                                </td>
                                <td>{{ $line->description ?: '-' }}</td>
                                <td class="text-right text-success">
                                    {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-right text-danger">
                                    {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="2" class="text-right">TOTAL</td>
                                <td class="text-right text-primary">Rp {{ number_format($journal->lines->sum('debit'), 0, ',', '.') }}</td>
                                <td class="text-right text-primary">Rp {{ number_format($journal->lines->sum('credit'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5>Keterangan Umum:</h5>
                    <p class="lead">{{ $journal->description }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Audit Trail</h3>
                </div>
                <div class="card-body p-2">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Dibuat Oleh: <span>{{ $journal->creator->name ?? 'System' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tgl Buat: <span>{{ $journal->created_at->format('d/m/y H:i') }}</span>
                        </li>
                        @if($journal->updated_by)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Diupdate Oleh: <span>{{ $journal->updater->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tgl Update: <span>{{ $journal->updated_at->format('d/m/y H:i') }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card mt-3 card-danger card-outline">
                <div class="card-body text-center">
                    <p class="text-muted small">Tindakan Berbahaya</p>
                    <form action="{{ route('journals.destroy', $journal) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block btn-sm" onclick="return confirm('Hapus transaksi ini? (Soft Delete)')">
                            <i class="fas fa-trash mr-1"></i> Hapus Transaksi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
