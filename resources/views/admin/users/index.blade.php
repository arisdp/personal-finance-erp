@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')
<h1>User Management</h1>
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
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-{{ $user->role == 'super_admin' ? 'danger' : 'info' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        <!-- <a href="{{ route('admin.users.edit', $user->id) }}"
                            class="btn btn-sm btn-warning">Edit</a> -->

                        <form action="{{ route('admin.users.destroy', $user) }}"
                            method="POST"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete user?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $users->links() }}

    </div>
</div>

@stop