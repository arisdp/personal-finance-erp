@extends('adminlte::page')

@section('title', 'Workspaces')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Manajemen Workspace</h1>
        </div>
        <div class="col-sm-6 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#createWorkspaceModal">
                <i class="fas fa-plus"></i> Tambah Workspace
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Daftar Workspace Anda</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama Workspace</th>
                                <th>Role Anda</th>
                                <th>Status</th>
                                <th style="width: 250px" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workspaces as $workspace)
                                <tr>
                                    <td class="align-middle">
                                        <strong>{{ $workspace->name }}</strong>
                                        <br>
                                        <small class="text-muted">ID: {{ $workspace->slug }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($workspace->pivot->role === 'owner')
                                            <span class="badge badge-primary">Owner</span>
                                        @elseif($workspace->pivot->role === 'editor')
                                            <span class="badge badge-success">Editor</span>
                                        @else
                                            <span class="badge badge-secondary">Viewer</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if(session('active_workspace_id') === $workspace->id)
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Sedang Aktif</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <!-- Switch Button -->
                                        @if(session('active_workspace_id') !== $workspace->id)
                                            <form action="{{ route('workspaces.switch', $workspace) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info" title="Gunakan Workspace Ini">
                                                    <i class="fas fa-exchange-alt"></i> Gunakan
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Edit Button (Only for Owners/Editors to view members) -->
                                        <a href="{{ route('workspaces.edit', $workspace) }}" class="btn btn-sm btn-warning" title="Kelola Anggota / Pengaturan">
                                            <i class="fas fa-cog"></i> Kelola
                                        </a>

                                        <!-- Delete Button (Only for Owner) -->
                                        @if($workspace->pivot->role === 'owner' && auth()->user()->workspaces()->count() > 1)
                                            <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus workspace ini secara permanen? Semua data di dalamnya akan hilang.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Workspace">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <span class="text-muted">Anda belum memiliki workspace satupun.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Workspace Modal -->
    <div class="modal fade" id="createWorkspaceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('workspaces.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Workspace Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Workspace</label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="Contoh: Keuangan Keluarga 2026">
                            <small class="form-text text-muted">Workspace digunakan untuk memisahkan data pembukuan Anda secara total.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
