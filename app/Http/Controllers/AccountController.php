<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())
            ->orderBy('code')
            ->paginate(15);

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parents = Account::where('user_id', auth()->id())
            ->orderBy('code')
            ->get();

        return view('accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:accounts,code',
            'name' => 'required',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id'
        ]);

        Account::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully');
    }

    public function edit(Account $account)
    {
        abort_if($account->user_id !== auth()->id(), 403);

        $parents = Account::where('user_id', auth()->id())
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code' => 'required|unique:accounts,code,' . $account->id,
            'name' => 'required',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id'
        ]);

        $account->update($validated);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account updated successfully');
    }

    public function destroy(Account $account)
    {
        abort_if($account->user_id !== auth()->id(), 403);

        if ($account->journalLines()->exists()) {
            return back()->with('error', 'Cannot delete account with transactions');
        }

        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully');
    }
}
