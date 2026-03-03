<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        $workspaceId = session('active_workspace_id');
        $budgets = Budget::where('workspace_id', $workspaceId)
            ->with('account')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        $accounts = Account::where('category', 'expense')->orderBy('code')->get();
        return view('budgets.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $workspaceId = session('active_workspace_id');

        Budget::create([
            'workspace_id' => $workspaceId,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'year' => $request->year,
            'month' => $request->month,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('budgets.index')->with('success', 'Budget created successfully');
    }

    public function edit(Budget $budget)
    {
        $accounts = Account::where('category', 'expense')->orderBy('code')->get();
        return view('budgets.edit', compact('budget', 'accounts'));
    }

    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $budget->update($request->all());

        return redirect()->route('budgets.index')->with('success', 'Budget updated successfully');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully');
    }
}
