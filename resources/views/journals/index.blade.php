@extends('adminlte::page')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Journal Entry</h4>
    </div>

    <div class="card-body">

        <form id="journalForm">
            @csrf

            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-9">
                    <input type="text" name="description"
                        class="form-control" placeholder="Description" required>
                </div>
            </div>

            <table class="table table-bordered" id="journalTable">
                <thead>
                    <tr>
                        <th width="40%">Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <button type="button" class="btn btn-info" id="addRow">
                + Add Line
            </button>

            <hr>

            <button type="submit" class="btn btn-success">
                Save Journal
            </button>

        </form>

    </div>
</div>

@endsection

@section('js')
<script>
    let rowIndex = 0;

    function addRow() {
        let row = `
    <tr>
        <td>
            <select name="lines[${rowIndex}][account_id]" class="form-control" required>
                <option value="">-- Select Account --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">
                        {{ $account->code }} - {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" step="0.01" 
                   name="lines[${rowIndex}][debit]" 
                   class="form-control debit">
        </td>
        <td>
            <input type="number" step="0.01" 
                   name="lines[${rowIndex}][credit]" 
                   class="form-control credit">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm removeRow">
                x
            </button>
        </td>
    </tr>
    `;

        $('#journalTable tbody').append(row);
        rowIndex++;
    }

    $('#addRow').click(function() {
        addRow();
    });

    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
    });

    $('#journalForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('journals.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.href = "{{ route('journals.index') }}";
            },
            error: function(err) {
                alert('Validation failed or journal not balanced');
            }
        });
    });

    addRow();
    addRow();
</script>
@endsection