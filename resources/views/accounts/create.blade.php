@extends('adminlte::page')

@section('title', 'Create Account')

@section('content')

<div class="card">
    <div class="card-body">

        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <div class="form-group mb-3">
                <label>Category</label>
                <select name="category" class="form-control" required>
                    @foreach(['asset','liability','equity','income','expense'] as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            @include('accounts.partials.form')

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