@extends('adminlte::page')

@section('title', 'Chart of Accounts')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Chart of Accounts (COA)</h1>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Account
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="200">Code</th>
                            <th>Account Name</th>
                            <th width="150">Type</th>
                            <th width="150">Category</th>
                            <th width="200" class="text-right">Balance (Recursive)</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            @include('accounts.partials.account_row', [
                                'account' => $account,
                                'level' => 0,
                            ])
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle !important;
        }

        code {
            font-size: 1rem;
            color: #e83e8c;
        }

        .child-row {
            transition: all 0.3s ease;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.toggle-children').on('click', function() {
                const icon = $(this);
                const targetId = icon.data('target');

                if (icon.hasClass('fa-caret-right')) {
                    // Expanding: Show direct children and switch icon
                    icon.removeClass('fa-caret-right').addClass('fa-caret-down');
                    $('tr[data-parent="' + targetId + '"]').removeClass('d-none');
                } else {
                    // Collapsing: Switch icon and recursively hide ALL descendants
                    icon.removeClass('fa-caret-down').addClass('fa-caret-right');
                    hideDescendants(targetId);
                }
            });

            function hideDescendants(parentId) {
                let children = $('tr[data-parent="' + parentId + '"]');
                children.addClass('d-none');

                children.each(function() {
                    // If the child also has children, recursively hide them and reset the icon
                    let childIcon = $(this).find('.toggle-children');
                    if (childIcon.length > 0) {
                        childIcon.removeClass('fa-caret-down').addClass('fa-caret-right');
                    }

                    let childId = $(this).data('id');
                    if (childId) {
                        hideDescendants(childId);
                    }
                });
            }
        });
    </script>
@stop
