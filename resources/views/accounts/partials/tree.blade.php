<li class="nav-item {{ $account->children->count() ? 'has-treeview' : '' }}">

    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-folder"></i>
        <p>
            {{ $account->name }}
            <span class="float-right">
                Rp {{ number_format($account->total_balance) }}
            </span>

            @if($account->children->count())
            <i class="right fas fa-angle-left"></i>
            @endif
        </p>
    </a>

    @if($account->children->count())
    <ul class="nav nav-treeview">

        @foreach($account->children as $child)
        @include('accounts.partials.tree', ['account' => $child])
        @endforeach

    </ul>
    @endif

</li>