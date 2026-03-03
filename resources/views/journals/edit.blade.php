@extends('adminlte::page')

@section('title', 'Edit Transaksi')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Jurnal: {{ $journal->reference }}</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('journals.show', $journal) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Batal
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Form Koreksi Jurnal</h3>
        </div>
        <div class="card-body">
            <form id="editJournalForm">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label>Tanggal Transaksi</label>
                        <input type="date" name="date" class="form-control" value="{{ $journal->date }}" required>
                    </div>
                    <div class="col-md-9">
                        <label>Keterangan Umum</label>
                        <input type="text" name="description" class="form-control" value="{{ $journal->description }}" required>
                    </div>
                </div>

                <table class="table table-bordered table-striped" id="journalTable">
                    <thead class="bg-light">
                        <tr>
                            <th width="35%">Akun</th>
                            <th width="30%">Keterangan Baris (Opsional)</th>
                            <th width="15%">Debit</th>
                            <th width="15%">Kredit</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->lines as $index => $line)
                        <tr>
                            <td>
                                <select name="lines[{{ $index }}][account_id]" class="form-control select2" required>
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ $line->account_id == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="lines[{{ $index }}][description]" class="form-control" value="{{ $line->description }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="lines[{{ $index }}][debit]" class="form-control debit" value="{{ (float)$line->debit }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="lines[{{ $index }}][credit]" class="form-control credit" value="{{ (float)$line->credit }}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right font-weight-bold">SELISIH (BALANCE):</td>
                            <td colspan="2" class="text-center">
                                <span id="balanceInfo" class="badge badge-success px-3 py-2" style="font-size: 1rem;">BALANCE: 0</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="mt-3">
                    <button type="button" class="btn btn-info" id="addRow">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>

                <hr>

                <div class="text-right">
                    <button type="submit" class="btn btn-warning btn-lg px-5" id="btnUpdate">
                        <i class="fas fa-save mr-1"></i> Update Transaksi
                    </button>
                </div>
            </form>
        </div>
        
        <div class="overlay dark d-none" id="formOverlay">
            <i class="fas fa-2x fa-spinner fa-spin"></i>
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
        let rowIndex = {{ $journal->lines->count() }};

        function initSelect2() {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        }

        initSelect2();

        function calculateBalance() {
            let totalDebit = 0;
            let totalCredit = 0;
            $('.debit').each(function() { totalDebit += parseFloat($(this).val()) || 0; });
            $('.credit').each(function() { totalCredit += parseFloat($(this).val()) || 0; });

            let diff = Math.abs(totalDebit - totalCredit);
            if (diff < 0.01) {
                $('#balanceInfo').removeClass('badge-danger').addClass('badge-success').text('BALANCE SEIMBANG');
                $('#btnUpdate').prop('disabled', false);
            } else {
                $('#balanceInfo').removeClass('badge-success').addClass('badge-danger').text('TIDAK SEIMBANG (Selisih: ' + diff.toLocaleString() + ')');
                $('#btnUpdate').prop('disabled', true);
            }
        }

        $(document).on('input', '.debit, .credit', calculateBalance);
        
        $('#addRow').click(function() {
            let row = `
            <tr>
                <td>
                    <select name="lines[${rowIndex}][account_id]" class="form-control select2" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="lines[${rowIndex}][description]" class="form-control"></td>
                <td><input type="number" step="0.01" name="lines[${rowIndex}][debit]" class="form-control debit" value="0"></td>
                <td><input type="number" step="0.01" name="lines[${rowIndex}][credit]" class="form-control credit" value="0"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fas fa-times"></i></button></td>
            </tr>`;
            $('#journalTable tbody').append(row);
            initSelect2();
            rowIndex++;
            calculateBalance();
        });

        $(document).on('click', '.removeRow', function() {
            $(this).closest('tr').remove();
            calculateBalance();
        });

        $('#editJournalForm').submit(function(e) {
            e.preventDefault();
            $('#formOverlay').removeClass('d-none');
            $('#btnUpdate').prop('disabled', true);

            $.ajax({
                url: "{{ route('journals.update', $journal) }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    window.location.href = "{{ route('journals.show', $journal) }}";
                },
                error: function(err) {
                    $('#formOverlay').addClass('d-none');
                    $('#btnUpdate').prop('disabled', false);
                    alert('Gagal update: ' + (err.responseJSON?.message || 'Server error'));
                }
            });
        });

        calculateBalance();
    });
</script>
@stop
