<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function index()
    {
        // Ambil hanya root accounts (yang tidak punya parent)
        $accounts = Account::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('code');
            }])
            ->orderBy('code')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parents = Account::orderBy('code')->get();

        return view('accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:accounts,code',
            'name' => 'required',
            'type' => 'required',
            'category' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
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
        $parents = Account::where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'code' => 'required|unique:accounts,code,' . $account->id,
            'name' => 'required',
            'type' => 'required',
            'category' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
        ]);

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
