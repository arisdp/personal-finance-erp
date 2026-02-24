@extends('adminlte::page')

@section('title', 'Accounts')

@section('content_header')
<h1>Account Management</h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        <a href="{{ route('accounts.create') }}"
            class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Account
        </a>
    </div>

    <div class="card-body">
        <div class="mb-3">
            <select id="categoryFilter" class="form-control" style="width:200px;">
                <option value="">All Category</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
            </select>
        </div>

        <table id="accountsTable"
            class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Parent</th>
                    <th>Balance</th>
                    <th width="120">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->id }}</td>
                    <td>{{ $account->name }}</td>
                    <td>{{ ucfirst($account->type) }}</td>
                    <td>{{ ucfirst($account->category ?? 'asset') }}</td>
                    <td>{{ $account->parent?->name ?? '-' }}</td>
                    <td>{{ number_format($account->balance) }}</td>
                    <td>
                        <a href="{{ route('accounts.edit', $account) }}"
                            class="btn btn-warning btn-xs">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('accounts.destroy', $account) }}"
                            method="POST"
                            style="display:inline">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-xs"
                                onclick="return confirm('Delete this account?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

@stop

@section('js')
<script>
    $(function() {

        var table = $("#accountsTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            "pageLength": 10,
        });

        // Filter by category
        $('#categoryFilter').on('change', function() {
            table.column(3).search(this.value).draw();
        });

    });
</script>
@stop