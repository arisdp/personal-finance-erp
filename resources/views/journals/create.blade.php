@extends('adminlte::page')

@section('title', 'Catat Transaksi Baru')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Catat Transaksi</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('journals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-outline card-success">
                <div class="card-header p-2">
                    <ul class="nav nav-pills" id="transactionTabs">
                        <li class="nav-item"><a class="nav-link active" href="#expense" data-toggle="tab" data-type="expense"><i class="fas fa-arrow-down text-danger mr-1"></i> Pengeluaran</a></li>
                        <li class="nav-item"><a class="nav-link" href="#income" data-toggle="tab" data-type="income"><i class="fas fa-arrow-up text-success mr-1"></i> Pemasukan</a></li>
                        <li class="nav-item"><a class="nav-link" href="#transfer" data-toggle="tab" data-type="transfer"><i class="fas fa-exchange-alt text-info mr-1"></i> Transfer Kas</a></li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <form id="transactionForm">
                        @csrf
                        <input type="hidden" name="transaction_type" id="transaction_type" value="expense">
                        
                        <!-- 1. General Info -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Tanggal</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-8">
                                <label>Keterangan <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" placeholder="Contoh: Beli Bensin, Gaji Bulanan, dll." required>
                            </div>
                        </div>

                        <!-- 2. The Dynamic Single-Entry UI -->
                        <div class="row mb-4 bg-light p-3 rounded border">
                            <!-- Account A (From Kas / To Kas depending on type) -->
                            <div class="col-md-5">
                                <label id="labelAccountA">Sumber Dana (Dari)</label>
                                <select name="account_a" id="account_a" class="form-control select2" required>
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" data-category="{{ $account->category }}" data-code="{{ $account->code }}">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 text-center align-self-center">
                                <i class="fas fa-arrow-right fa-2x text-muted mt-4"></i>
                            </div>

                            <!-- Account B (Category Expense/Income/Asset depending on type) -->
                            <div class="col-md-5">
                                <label id="labelAccountB">Tujuan (Untuk)</label>
                                <select name="account_b" id="account_b" class="form-control select2" required>
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" data-category="{{ $account->category }}" data-code="{{ $account->code }}">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 3. Amount -->
                        <div class="row">
                            <div class="col-md-6 mx-auto">
                                <label class="text-center d-block">Nominal (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">Rp</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" class="form-control text-right font-weight-bold" required min="1" placeholder="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-success btn-lg px-5" id="btnSubmit">
                                <i class="fas fa-save mr-1"></i> Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div> <!-- /.card-body -->
                
                <!-- Loading Overlay -->
                <div class="overlay dark d-none" id="formOverlay">
                    <i class="fas fa-2x fa-spinner fa-spin"></i>
                </div>
            </div> <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 Make it look nicer
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // UI Logic for switching Transaction Types
        function updateFormUI(type) {
            $('#transaction_type').val(type);
            
            // Reset Selections
            $('#account_a').val('').trigger('change');
            $('#account_b').val('').trigger('change');

            if (type === 'expense') {
                $('#labelAccountA').text('Sumber Kas/Bank (Kredit)');
                $('#labelAccountB').text('Kategori Pengeluaran (Debit)');
                
                // Hide non asset/liability on A, hide non expense on B (Simplified UI representation)
                filterOptions('#account_a', ['asset', 'liability']);
                filterOptions('#account_b', ['expense']);

            } else if (type === 'income') {
                $('#labelAccountA').text('Sumber Pendapatan (Kredit)');
                $('#labelAccountB').text('Penyimpanan Kas/Bank (Debit)');

                filterOptions('#account_a', ['income']);
                filterOptions('#account_b', ['asset']);

            } else if (type === 'transfer') {
                $('#labelAccountA').text('Dari Kas/Bank (Kredit)');
                $('#labelAccountB').text('Ke Kas/Bank (Debit)');

                filterOptions('#account_a', ['asset', 'liability']);
                filterOptions('#account_b', ['asset', 'liability']);
            }
        }

        function filterOptions(selectId, allowedCategories) {
            $(selectId + ' option').each(function() {
                let cat = $(this).data('category');
                if (cat) { // Skip placeholder
                    if (allowedCategories.includes(cat)) {
                        $(this).prop('disabled', false);
                    } else {
                        $(this).prop('disabled', true);
                    }
                }
            });
            // Re-render select2 to reflect disabled state
            $(selectId).select2({theme: 'bootstrap4'});
        }

        // Handle Tab Clicks
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let type = $(e.target).data('type');
            updateFormUI(type);
        });

        // Initialize state
        updateFormUI('expense');

        // Form Submission logic
        $('#transactionForm').on('submit', function(e) {
            e.preventDefault();

            let type = $('#transaction_type').val();
            let amount = parseFloat($('#amount').val());
            let accA = $('#account_a').val();
            let accB = $('#account_b').val();

            if (!accA || !accB) {
                alert('Silakan pilih kedua akun dengan benar.');
                return;
            }
            if (accA === accB) {
                alert('Akun asal dan tujuan tidak boleh sama.');
                return;
            }
            if (amount <= 0 || isNaN(amount)) {
                alert('Nominal harus lebih dari 0.');
                return;
            }

            // Build double entry payload dynamically based on Type
            // Rule:
            // Debit = account_b (The one receiving value/incurring expense)
            // Credit = account_a (The source of funds/income)
            
            let lines = [
                { account_id: accA, credit: amount, debit: 0 },
                { account_id: accB, debit: amount, credit: 0 }
            ];

            let payload = {
                _token: $('input[name="_token"]').val(),
                date: $('input[name="date"]').val(),
                description: $('input[name="description"]').val(),
                lines: lines
            };

            // Loading state
            $('#formOverlay').removeClass('d-none');
            $('#btnSubmit').prop('disabled', true);

            $.ajax({
                url: "{{ route('journals.store') }}",
                method: "POST",
                data: payload,
                success: function(res) {
                    alert('Transaksi Berhasil Disimpan!');
                    window.location.href = "{{ route('journals.index') }}";
                },
                error: function(err) {
                    $('#formOverlay').addClass('d-none');
                    $('#btnSubmit').prop('disabled', false);
                    let msg = err.responseJSON?.message || 'Gagal menyimpan transaksi!';
                    alert('Error: ' + msg);
                }
            });
        });

    });
</script>
@stop
