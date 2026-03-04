<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\AssetHolding;
use App\Models\AssetPrice;
use App\Models\Installment;
use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function create()
    {
        $workspaceId = session('active_workspace_id');
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();
        
        // Group accounts for easier selection
        $assetAccounts = $accounts->where('category', 'asset');
        $liabilityAccounts = $accounts->where('category', 'liability');
        $incomeAccounts = Account::where('type', 'income')->where('is_postable', true)->orderBy('code')->get();
        $expenseAccounts = Account::where('type', 'expense')->where('is_postable', true)->orderBy('code')->get();

        $activeInstallments = Installment::where('workspace_id', $workspaceId)->where('status', 'active')->get();
        $recurringTransactions = RecurringTransaction::where('workspace_id', $workspaceId)->where('is_active', true)->get();

        return view('transactions.create', compact(
            'assetAccounts', 
            'liabilityAccounts', 
            'incomeAccounts', 
            'expenseAccounts', 
            'accounts',
            'activeInstallments',
            'recurringTransactions'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:expense,income,transfer,investment,debt_payment,initial_balance',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            // Specific validation based on type
            'from_account_id' => 'required_if:type,expense,transfer,investment,debt_payment,initial_balance|exists:accounts,id',
            'to_account_id' => 'required_if:type,income,transfer,investment,debt_payment,initial_balance|exists:accounts,id',
            // Investment specific
            'asset_name' => 'required_if:type,investment|nullable|string|max:255',
            'asset_type' => 'required_if:type,investment|nullable|string',
            'quantity' => 'required_if:type,investment|nullable|numeric|min:0',
            // Debt payment specific
            'expense_account_id' => 'required_if:type,debt_payment|nullable|exists:accounts,id',
            'installment_id' => 'nullable|exists:installments,id',
            'recurring_transaction_id' => 'nullable|exists:recurring_transactions,id',
        ]);

        $workspaceId = session('active_workspace_id');

        try {
            DB::transaction(function () use ($request, $workspaceId) {
                // 1. Create Journal Entry
                $journal = JournalEntry::create([
                    'workspace_id' => $workspaceId,
                    'date' => $request->date,
                    'reference' => 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'description' => '[' . strtoupper($request->type) . '] ' . $request->description,
                ]);

                if ($request->type === 'debt_payment') {
                    // Logic for Debt Payment (Double Recognition: Liability reduction + Expense recording)
                    
                    // Pair A: Debit Liability, Credit Equity (Reduction of Debt Balance)
                    $equityAccount = Account::where('code', '3100')->first(); // Modal/Equity as bridge
                    
                    $journal->lines()->create([
                        'account_id' => $request->to_account_id, // The Liability account (CC/KPR)
                        'debit' => $request->amount,
                        'credit' => 0,
                        'description' => "Penyesuaian Saldo Hutang: {$request->description}"
                    ]);

                    $journal->lines()->create([
                        'account_id' => $equityAccount->id,
                        'debit' => 0,
                        'credit' => $request->amount,
                        'description' => "Penyesuaian Kontra Pembayaran Hutang"
                    ]);

                    // Pair B: Debit Expense, Credit Cash/Bank (The actual outgoing cash)
                    $journal->lines()->create([
                        'account_id' => $request->expense_account_id, // The Expense Category
                        'debit' => $request->amount,
                        'credit' => 0,
                        'description' => $request->description
                    ]);

                    $journal->lines()->create([
                        'account_id' => $request->from_account_id, // Cash/Bank
                        'debit' => 0,
                        'credit' => $request->amount,
                        'description' => $request->description
                    ]);

                } else {
                    // Standard Transaction logic
                    $debitAccountId = null;
                    $creditAccountId = null;

                    switch ($request->type) {
                        case 'expense':
                            $debitAccountId = $request->to_account_id;
                            $creditAccountId = $request->from_account_id;
                            break;
                        case 'income':
                            $debitAccountId = $request->to_account_id;
                            $creditAccountId = $request->from_account_id;
                            break;
                        case 'initial_balance':
                            $debitAccountId = $request->to_account_id;
                            $creditAccountId = $request->from_account_id;
                            break;
                        case 'transfer':
                            $debitAccountId = $request->to_account_id;
                            $creditAccountId = $request->from_account_id;
                            break;
                        case 'investment':
                            $debitAccountId = $request->to_account_id;
                            $creditAccountId = $request->from_account_id;
                            $this->handleInvestmentRecording($request, $workspaceId, $debitAccountId);
                            break;
                    }

                    $journal->lines()->create([
                        'account_id' => $debitAccountId,
                        'debit' => $request->amount,
                        'credit' => 0,
                        'description' => $request->description
                    ]);

                    $journal->lines()->create([
                        'account_id' => $creditAccountId,
                        'debit' => 0,
                        'credit' => $request->amount,
                        'description' => $request->description
                    ]);
                }

                // Update related records (Installments / Recurring Bills)
                if ($request->filled('installment_id')) {
                    $inst = Installment::find($request->installment_id);
                    if ($inst) {
                        $inst->decrement('remaining_periods');
                        if ($inst->remaining_periods <= 0) {
                            $inst->update(['status' => 'completed']);
                        }
                    }
                }

                if ($request->filled('recurring_transaction_id')) {
                    $rt = RecurringTransaction::find($request->recurring_transaction_id);
                    if ($rt) {
                        // Dynamically update next due date based on frequency
                        $nextDate = Carbon::parse($rt->next_due_date);
                        if ($rt->frequency === 'monthly') {
                            $nextDate->addMonth();
                        } elseif ($rt->frequency === 'weekly') {
                            $nextDate->addWeek();
                        } elseif ($rt->frequency === 'yearly') {
                            $nextDate->addYear();
                        }

                        $rt->update([
                            'next_due_date' => $nextDate,
                            'last_posted_date' => $request->date
                        ]);
                    }
                }
            });

            return redirect()->route('journals.index')->with('success', 'Transaksi berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function handleInvestmentRecording($request, $workspaceId, $accountId)
    {
        // Check if holding exists for this account & asset_name
        $holding = AssetHolding::where('workspace_id', $workspaceId)
            ->where('account_id', $accountId)
            ->where('asset_name', $request->asset_name)
            ->first();

        $price = $request->amount / ($request->quantity ?: 1);

        if ($holding) {
            // Update existing holding
            $totalQty = $holding->quantity + $request->quantity;
            $totalCost = ($holding->quantity * $holding->avg_buy_price) + $request->amount;
            $newAvgPrice = $totalQty > 0 ? $totalCost / $totalQty : $holding->avg_buy_price;

            $holding->update([
                'quantity' => $totalQty,
                'avg_buy_price' => $newAvgPrice,
                'current_price' => $price, // Assume current price is what we just paid
                'last_updated' => Carbon::now(),
            ]);
        } else {
            // Create new holding
            AssetHolding::create([
                'workspace_id' => $workspaceId,
                'account_id' => $accountId,
                'asset_name' => $request->asset_name,
                'asset_type' => $request->asset_type ?? 'stock', // Dynamic from form
                'ticker' => null, // Not heavily used yet
                'quantity' => $request->quantity,
                'avg_buy_price' => $price,
                'current_price' => $price,
                'last_updated' => Carbon::now(),
            ]);
        }

        // Add to historical prices
        AssetPrice::create([
            'account_id' => $accountId,
            'asset_type' => $request->asset_type ?? 'stock',
            'ticker' => $request->asset_name,
            'price' => $price,
            'price_date' => $request->date,
            'source' => 'Auto From Transaction',
        ]);
    }
}
