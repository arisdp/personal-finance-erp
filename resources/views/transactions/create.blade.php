@extends('adminlte::page')

@section('title', 'Smart Transaction')

@section('content_header')
    <h1><i class="fas fa-magic mr-2"></i> Transaksi Baru</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-outline card-primary shadow">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Tanggal</label>
                                <input type="date" name="date" id="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Jenis Transaksi</label>
                                <select name="type" id="type" class="form-control select2" required onchange="toggleFormFields()">
                                    <option value="expense">📉 Pengeluaran (Belanja/Tagihan)</option>
                                    <option value="income">📈 Pemasukan (Gaji/Bonus)</option>
                                    <option value="transfer">🔄 Transfer Antar Rekening</option>
                                    <option value="investment">💎 Pembelian Investasi (Saham/Emas)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Keterangan / Memo</label>
                        <input type="text" name="description" id="description" class="form-control" placeholder="Beli apa? / Dari siapa?" required>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="amount_display">Nominal (Rp)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">Rp</span>
                                    </div>
                                    <input type="text" id="amount_display" class="form-control form-control-lg numeric-input" required>
                                    <input type="hidden" name="amount" id="amount_hidden">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Dynamic Section -->
                    <div class="row">
                        <div class="col-md-6" id="from_account_container">
                            <div class="form-group">
                                <label id="label_from">Sumber Dana (Kredit)</label>
                                <select name="from_account_id" id="from_account_id" class="form-control select2">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="to_account_container">
                            <div class="form-group">
                                <label id="label_to">Kategori / Tujuan (Debit)</label>
                                <select name="to_account_id" id="to_account_id" class="form-control select2">
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Investment Fields (Hidden by default) -->
                    <div id="investment_fields" class="mt-3 p-3 bg-light border rounded" style="display:none;">
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-chart-line mr-1"></i> Detail Investasi</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Aset / Ticker</label>
                                    <input type="text" name="ticker" class="form-control" placeholder="E.g. BBCA, Emas Antam">
                                    <input type="hidden" name="asset_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah (Qty)</label>
                                    <input type="number" name="quantity" class="form-control" step="0.000001" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save mr-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .numeric-input { font-size: 1.25rem; font-weight: bold; color: #007bff; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let amountAutoNumeric;

    $(document).ready(function() {
        $('.select2').select2({ width: '100%', theme: 'bootstrap4' });

        // Toggle fields on load
        toggleFormFields();
    });

    function toggleFormFields() {
        const type = $('#type').val();
        const investmentFields = $('#investment_fields');
        const labelFrom = $('#label_from');
        const labelTo = $('#label_to');

        // Reset display
        investmentFields.hide();
        
        if (type === 'expense') {
            labelFrom.text('Sumber Dana (Dari mana uangnya?)');
            labelTo.text('Kategori Pengeluaran (Untuk apa?)');
        } else if (type === 'income') {
            labelFrom.text('Sumber Pemasukan (Dari siapa?)');
            labelTo.text('Tujuan Dana (Masuk ke mana?)');
        } else if (type === 'transfer') {
            labelFrom.text('Rekening Sumber');
            labelTo.text('Rekening Tujuan');
        } else if (type === 'investment') {
            labelFrom.text('Metode Pembayaran (Funding)');
            labelTo.text('Akun Investasi');
            investmentFields.show();
        }
    }
</script>
@stop
