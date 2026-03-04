<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        $workspaceId = session('active_workspace_id');
        $recurringTransactions = RecurringTransaction::where('workspace_id', $workspaceId)
            ->with(['debitAccount', 'creditAccount'])
            ->orderBy('name')
            ->get();

        return view('recurring_transactions.index', compact('recurringTransactions'));
    }

    public function create()
    {
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();
        return view('recurring_transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'frequency' => 'required|in:monthly,weekly,yearly',
            'day_of_month' => 'nullable|integer|between:1,31',
            'next_due_date' => 'required|date',
        ]);

        $workspaceId = session('active_workspace_id');

        RecurringTransaction::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'debit_account_id' => $request->debit_account_id,
            'credit_account_id' => $request->credit_account_id,
            'frequency' => $request->frequency,
            'day_of_month' => $request->day_of_month,
            'next_due_date' => $request->next_due_date,
            'is_active' => $request->has('is_active'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('recurring.index')->with('success', 'Recurring transaction created successfully');
    }

    public function edit(RecurringTransaction $recurring)
    {
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();
        return view('recurring_transactions.edit', compact('recurring', 'accounts'));
    }

    public function update(Request $request, RecurringTransaction $recurring)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'frequency' => 'required|in:monthly,weekly,yearly',
            'day_of_month' => 'nullable|integer|between:1,31',
            'next_due_date' => 'required|date',
        ]);

        $recurring->update([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'debit_account_id' => $request->debit_account_id,
            'credit_account_id' => $request->credit_account_id,
            'frequency' => $request->frequency,
            'day_of_month' => $request->day_of_month,
            'next_due_date' => $request->next_due_date,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('recurring.index')->with('success', 'Recurring transaction updated successfully');
    }

    public function destroy(RecurringTransaction $recurring)
    {
        $recurring->delete();
        return redirect()->route('recurring.index')->with('success', 'Recurring transaction deleted successfully');
    }
}
