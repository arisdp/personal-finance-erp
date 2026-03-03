@php $level = $level ?? 0; @endphp
<tr>
    <td style="padding-left: {{ $level * 30 + 10 }}px;">
        @if($account->children->count() > 0)
            <i class="fas fa-caret-down text-muted mr-1"></i>
        @else
            <i class="fas fa-minus text-muted mr-2 small"></i>
        @endif
        <code>{{ $account->code }}</code>
    </td>
    <td>
        <span class="{{ $level == 0 ? 'font-weight-bold' : '' }}">
            {{ $account->name }}
        </span>
        @if(!$account->is_postable)
            <span class="badge badge-light ml-1 border">Parent</span>
        @endif
    </td>
    <td><span class="badge badge-info">{{ ucfirst($account->type) }}</span></td>
    <td>{{ ucfirst($account->category) }}</td>
    <td class="text-right {{ $account->total_balance < 0 ? 'text-danger' : '' }}">
        <span class="{{ $level == 0 ? 'font-weight-bold' : '' }}">
            {{ 'Rp ' . number_format($account->total_balance, 0, ',', '.') }}
        </span>
    </td>
    <td class="text-center">
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus akun ini?')"><i class="fas fa-trash"></i></button>
        </form>
    </td>
</tr>

@if($account->children->count() > 0)
    @foreach($account->children->sortBy('code') as $child)
        @include('accounts.partials.account_row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif
