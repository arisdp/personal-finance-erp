@extends('adminlte::page')

@section('title', 'Create Account')

@section('content_header')
<h1>Create Account</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf

            @include('accounts.partials.form')

            <button class="btn btn-success">Save</button>
        </form>
    </div>
</div>

@stop