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
                    @if($errors->any())
                        <div class="alert alert-danger mb-4 rounded">
                            <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal Menyimpan!</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
                                    <option value="debt_payment">💳 Pembayaran Cicilan / Hutang / Tagihan</option>
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
                    <div id="investment_fields" class="mt-3 p-3 bg-light border rounded dynamic-field-section" style="display:none;">
                        <h6 class="font-weight-bold mb-3 text-primary"><i class="fas fa-chart-line mr-1"></i> Detail Investasi</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Aset / Ticker</label>
                                    <input type="text" name="asset_name" class="form-control" placeholder="E.g. BBCA, Emas Antam">
                                    <small class="text-muted">Nama aset atau kode saham.</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipe Aset</label>
                                    <select name="asset_type" class="form-control">
                                        <option value="gold">Emas</option>
                                        <option value="stock">Saham</option>
                                        <option value="crypto">Crypto</option>
                                        <option value="mutual_fund">Reksadana</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jumlah (Qty)</label>
                                    <input type="number" name="quantity" class="form-control" step="0.000001" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Debt & Bill Payment Fields (Hidden by default) -->
                    <div id="debt_fields" class="mt-3 p-3 bg-light border rounded dynamic-field-section" style="display:none;">
                        <h6 class="font-weight-bold mb-3 text-danger"><i class="fas fa-credit-card mr-1"></i> Detail Pembayaran Hutang / Tagihan</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info py-2 small">
                                    <i class="fas fa-info-circle mr-1"></i> Fitur ini akan mencatat <strong>Pengurangan Hutang</strong> (limit kembali) sekaligus mencatatnya sebagai <strong>Pengeluaran</strong> bulan berjalan.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pilih Kategori Pengeluaran</label>
                                    <select name="expense_account_id" id="expense_account_id" class="form-control select2">
                                        <option value="">-- Pilih Kategori Beban --</option>
                                        @foreach($expenseAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Akun beban (misal: Beban Cicilan KPR, Bayar CC).</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hubungkan ke Data Aktif (Opsional)</label>
                                    <select name="link_type" id="link_type" class="form-control mb-2" onchange="toggleLinkageFields()">
                                        <option value="none">-- Tanpa Linkage --</option>
                                        <option value="installment">🔗 Cicilan Aktif (KPR/Kendaraan)</option>
                                        <option value="recurring">🔗 Tagihan Rutin (Listrik/Internet)</option>
                                    </select>
                                    
                                    <div id="link_installment_container" style="display:none;">
                                        <select name="installment_id" class="form-control select2">
                                            <option value="">-- Pilih Cicilan --</option>
                                            @foreach($activeInstallments as $inst)
                                                <option value="{{ $inst->id }}">{{ $inst->name }} (Sisa {{ $inst->remaining_periods }} bln)</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="link_recurring_container" style="display:none;">
                                        <select name="recurring_transaction_id" class="form-control select2">
                                            <option value="">-- Pilih Tagihan Rutin --</option>
                                            @foreach($recurringTransactions as $rt)
                                                <option value="{{ $rt->id }}">{{ $rt->name }} (Rp {{ number_format($rt->amount, 0, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
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
        const debtFields = $('#debt_fields');
        const labelFrom = $('#label_from');
        const labelTo = $('#label_to');

        // Reset display
        $('.dynamic-field-section').hide();
        
        if (type === 'expense') {
            labelFrom.text('Sumber Dana (Dari mana uangnya?)');
            labelTo.text('Kategori Pengeluaran (Untuk apa?)');
            debtFields.show();
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
        } else if (type === 'debt_payment') {
            labelFrom.text('Sumber Dana Pembayaran');
            labelTo.text('Akun Hutang / Kartu Kredit (Target)');
            debtFields.show();
        }
    }

    function toggleLinkageFields() {
        const linkType = $('#link_type').val();
        $('#link_installment_container').hide();
        $('#link_recurring_container').hide();

        if (linkType === 'installment') {
            $('#link_installment_container').show();
        } else if (linkType === 'recurring') {
            $('#link_recurring_container').show();
        }
    }
</script>
@stop
