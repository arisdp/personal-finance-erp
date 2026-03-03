@extends('adminlte::page')

@section('title', 'Kelola Workspace')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Kelola Workspace: {{ $workspace->name }}</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('workspaces.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">

        <!-- Pengaturan Dasar Workspace -->
        <div class="col-md-5">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Pengaturan Dasar</h3>
                </div>
                <div class="card-body">
                    @php 
                        $isOwner = $workspace->users->where('id', auth()->id())->first()?->pivot->role === 'owner';
                    @endphp

                    <form action="{{ route('workspaces.update', $workspace) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Nama Workspace</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $workspace->name }}" required {{ !$isOwner ? 'readonly' : '' }}>
                        </div>
                        
                        <div class="form-group">
                            <label>Workspace ID (Slug)</label>
                            <input type="text" class="form-control" value="{{ $workspace->slug }}" readonly>
                            <small class="text-muted">Digunakan sebagai identifier unik.</small>
                        </div>

                        <div class="form-group">
                            <label>Dibuat Pada</label>
                            <input type="text" class="form-control" value="{{ $workspace->created_at->format('d M Y H:i') }}" readonly>
                        </div>

                        @if($isOwner)
                            <button type="submit" class="btn btn-primary mt-2">
                                <i class="fas fa-save"></i> Ubah Nama Workspace
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Manajemen Anggota -->
        <div class="col-md-7">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Anggota Workspace</h3>
                    
                    @if(in_array($workspace->users->where('id', auth()->id())->first()?->pivot->role, ['owner', 'editor']))
                        <div class="card-tools">
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addMemberModal">
                                <i class="fas fa-user-plus"></i> Tambah Anggota
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Nama / Email</th>
                                <th>Role</th>
                                <th>Ditambahkan Pada</th>
                                @if($isOwner)
                                    <th class="text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workspace->users as $member)
                                <tr>
                                    <td>
                                        <strong>{{ $member->name }}</strong>
                                        @if($member->id === auth()->id())
                                            <span class="badge badge-info ml-1">Anda</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $member->email }}</small>
                                    </td>
                                    <td>
                                        @if($isOwner && $member->pivot->role !== 'owner')
                                            <!-- Role dropdown for owner to quickly change -->
                                            <form action="{{ route('workspaces.updateRole', [$workspace, $member]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="form-control form-control-sm" onchange="this.form.submit()" style="width: 100px;">
                                                    <option value="editor" {{ $member->pivot->role == 'editor' ? 'selected' : '' }}>Editor</option>
                                                    <option value="viewer" {{ $member->pivot->role == 'viewer' ? 'selected' : '' }}>Viewer</option>
                                                </select>
                                            </form>
                                        @else
                                            <!-- Just text for others or owner role -->
                                            @if($member->pivot->role === 'owner')
                                                <span class="badge badge-primary">Owner</span>
                                            @elseif($member->pivot->role === 'editor')
                                                <span class="badge badge-success">Editor</span>
                                            @else
                                                <span class="badge badge-secondary">Viewer</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($member->pivot->created_at)->format('d M Y') }}</small>
                                    </td>
                                    @if($isOwner)
                                        <td class="text-center">
                                            @if($member->pivot->role !== 'owner')
                                                <form action="{{ route('workspaces.removeMember', [$workspace, $member]) }}" method="POST" onsubmit="return confirm('Keluarkan pengguna ini dari workspace?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger py-0 px-2" title="Keluarkan dari Workspace">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Member -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('workspaces.addMember', $workspace) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Anggota ke Workspace</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="email">Email Pengguna</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="email@contoh.com">
                            <small class="form-text text-muted">Pastikan email rekan Anda sudah terdaftar di sistem.</small>
                        </div>
                        <div class="form-group">
                            <label for="role">Role / Peran</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="editor">Editor (Bisa mencatat jurnal & edit aset)</option>
                                <option value="viewer">Viewer (Hanya bisa melihat dashboard & laporan)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-user-plus"></i> Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
