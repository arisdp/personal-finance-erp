@extends('adminlte::page')

@section('title', 'Preview Bank Sync')

@section('content_header')
    <h1><i class="fas fa-search mr-2"></i> Preview Transaksi Bank</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('bank_sync.store') }}" method="POST">
            @csrf
            <input type="hidden" name="target_account_id" value="{{ $targetAccount->id }}">
            
            <div class="card card-outline card-success shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        Memproses File untuk Rekening: <strong>{{ $targetAccount->name }}</strong>
                    </h3>
                    <div class="card-tools">
                        <button type="submit" class="btn btn-success btn-flat">
                            <i class="fas fa-save mr-1"></i> Impor Transaksi Terpilih
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50"><input type="checkbox" id="checkAll" checked></th>
                                    <th>Tanggal</th>
                                    <th>Deskripsi Mutasi</th>
                                    <th class="text-right">Nominal</th>
                                    <th>Tipe</th>
                                    <th>Pilih Kategori (COA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $index => $trx)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="transactions[{{ $index }}][import]" checked class="trx-check">
                                            <input type="hidden" name="transactions[{{ $index }}][date]" value="{{ $trx['date'] }}">
                                            <input type="hidden" name="transactions[{{ $index }}][description]" value="{{ $trx['description'] }}">
                                            <input type="hidden" name="transactions[{{ $index }}][amount]" value="{{ $trx['amount'] }}">
                                            <input type="hidden" name="transactions[{{ $index }}][type]" value="{{ $trx['type'] }}">
                                        </td>
                                        <td>{{ $trx['date'] }}</td>
                                        <td>
                                            <span class="text-sm">{{ $trx['description'] }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold {{ $trx['type'] == 'CR' ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($trx['amount'], 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $trx['type'] == 'CR' ? 'badge-success' : 'badge-danger' }}">
                                                {{ $trx['type'] == 'CR' ? 'Kredit (Masuk)' : 'Debit (Keluar)' }}
                                            </span>
                                        </td>
                                        <td>
                                            <select name="transactions[{{ $index }}][to_account_id]" class="form-control form-control-sm select2">
                                                <option value="">-- Pilih Akun --</option>
                                                @foreach($expenseAccounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ Str::contains(strtoupper($trx['description']), strtoupper($acc->name)) ? 'selected' : '' }}>
                                                        {{ $acc->code }} - {{ $acc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('bank_sync.index') }}" class="btn btn-default mr-2">Batal</a>
                    <button type="submit" class="btn btn-success px-5">
                        <i class="fas fa-save mr-1"></i> Mulai Impor
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        $('#checkAll').on('change', function() {
            $('.trx-check').prop('checked', $(this).prop('checked'));
        });
    });
</script>
@stop
