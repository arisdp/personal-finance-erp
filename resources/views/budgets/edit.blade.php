@extends('adminlte::page')

@section('title', 'Edit Budget')

@section('content_header')
    <h1>Edit Anggaran</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-info">
            <form action="{{ route('budgets.update', $budget) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    @include('budgets.partials.__form')
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">Update Budget</button>
                    <a href="{{ route('budgets.index') }}" class="btn btn-link">Batal</a>
                </div>
            </form>
        </div>
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
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
        });
    });
</script>
@stop
