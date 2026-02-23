@extends('adminlte::page')

@section('title', 'Chart of Accounts')

@section('content_header')
<h1>Chart of Accounts</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Account
        </a>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->code }}</td>
                    <td>{{ $account->name }}</td>
                    <td>{{ ucfirst($account->type) }}</td>
                    <td>
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-warning">
                            Edit
                        </a>

                        <form action="{{ route('accounts.destroy', $account) }}"
                            method="POST"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this account?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $accounts->links() }}

    </div>
</div>

@stop