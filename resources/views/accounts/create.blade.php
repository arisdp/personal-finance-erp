@extends('adminlte::page')

@section('title', 'Create Account')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control">
                    <option value="bank">Bank</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="credit_card">Credit Card</option>
                </select>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" id="categorySelect" class="form-control">
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                </select>
            </div>

            <div class="form-group">
                <label>Parent</label>
                <select name="parent_id" id="parentSelect" class="form-control">
                    <option value="">-- None (Top Level) --</option>

                    @foreach($assets as $parent)
                    <option value="{{ $parent->id }}" data-category="asset">
                        {{ $parent->name }}
                    </option>
                    @endforeach

                    @foreach($liabilities as $parent)
                    <option value="{{ $parent->id }}" data-category="liability">
                        {{ $parent->name }}
                    </option>
                    @endforeach

                </select>
            </div>

            <div class="form-group">
                <label>Balance</label>
                <input type="number" name="balance" class="form-control" value="0">
            </div>

            <button class="btn btn-success">Save</button>

        </form>

    </div>
</div>
@stop

@section('js')
<script>
    $('#categorySelect').on('change', function() {
        var category = $(this).val();

        $('#parentSelect option').each(function() {
            if (!$(this).data('category') || $(this).data('category') === category) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('#parentSelect').val('');
    });
</script>
@stop