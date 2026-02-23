@extends('adminlte::page')

@section('title', 'Edit Account')

@section('content_header')
<h1>Edit Account</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ route('accounts.update', $account) }}" method="POST">
            @csrf
            @method('PUT')

            @include('accounts.partials.form')

            <button class="btn btn-success">Update</button>
        </form>
    </div>
</div>

@stop