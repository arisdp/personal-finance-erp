<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Installment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InstallmentController extends Controller
{
    public function index()
    {
        $workspaceId = session('active_workspace_id');
        $installments = Installment::where('workspace_id', $workspaceId)
            ->with('account')
            ->orderBy('status')
            ->orderBy('end_date')
            ->get();

        return view('installments.index', compact('installments'));
    }

    public function create()
    {
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();
        return view('installments.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'total_amount' => 'required|numeric|min:0',
            'monthly_amount' => 'required|numeric|min:0',
            'total_periods' => 'required|integer|min:1',
            'remaining_periods' => 'required|integer|min:0|max:'.$request->total_periods,
            'start_date' => 'required|date',
        ]);

        $workspaceId = session('active_workspace_id');

        Installment::create([
            'workspace_id' => $workspaceId,
            'account_id' => $request->account_id,
            'name' => $request->name,
            'total_amount' => $request->total_amount,
            'monthly_amount' => $request->monthly_amount,
            'total_periods' => $request->total_periods,
            'remaining_periods' => $request->remaining_periods,
            'start_date' => $request->start_date,
            'interest_rate' => $request->interest_rate ?? 0,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('installments.index')->with('success', 'Installment created successfully');
    }

    public function edit(Installment $installment)
    {
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();
        return view('installments.edit', compact('installment', 'accounts'));
    }

    public function update(Request $request, Installment $installment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'total_amount' => 'required|numeric|min:0',
            'monthly_amount' => 'required|numeric|min:0',
            'total_periods' => 'required|integer|min:1',
            'remaining_periods' => 'required|integer|min:0',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $installment->update($request->all());

        return redirect()->route('installments.index')->with('success', 'Installment updated successfully');
    }

    public function destroy(Installment $installment)
    {
        $installment->delete();
        return redirect()->route('installments.index')->with('success', 'Installment deleted successfully');
    }
}
