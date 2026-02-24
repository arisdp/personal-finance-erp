<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('parent')->latest()->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $assets = Account::where('category', 'asset')->get();
        $liabilities = Account::where('category', 'liability')->get();

        return view('accounts.create', compact('assets', 'liabilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'category' => 'required|in:asset,liability',
            'parent_id' => 'nullable|exists:accounts,id',
            'balance' => 'nullable|numeric'
        ]);

        // 🔒 CEK category parent harus sama
        if ($request->parent_id) {
            $parent = Account::find($request->parent_id);

            if ($parent->category !== $request->category) {
                return back()->withErrors('Parent category must match.');
            }
        }

        Account::create($request->all());

        return redirect()->route('accounts.index')
            ->with('success', 'Account created');
    }

    public function edit(Account $account)
    {
        $parents = Account::whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->get();

        return view('accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $account->update($request->all());
        return redirect()->route('accounts.index')
            ->with('success', 'Account updated');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return back()->with('success', 'Account deleted');
    }
}
