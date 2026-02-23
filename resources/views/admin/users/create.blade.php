@extends('adminlte::page')

@section('title', 'Create User')

@section('content')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users.partials.form')
            <button class="btn btn-success">Save</button>
        </form>
    </div>
</div>

@stop