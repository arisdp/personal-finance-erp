<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\AssetHolding;
use App\Models\AssetPrice;
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

        return view('transactions.create', compact('assetAccounts', 'liabilityAccounts', 'incomeAccounts', 'expenseAccounts', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:expense,income,transfer,investment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            // Specific validation based on type
            'from_account_id' => 'required_if:type,expense,transfer,investment|exists:accounts,id',
            'to_account_id' => 'required_if:type,income,transfer,investment|exists:accounts,id',
            // Investment specific
            'asset_name' => 'required_if:type,investment|string|max:255',
            'ticker' => 'nullable|string|max:20',
            'quantity' => 'required_if:type,investment|numeric|min:0',
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

                $debitAccountId = null;
                $creditAccountId = null;

                switch ($request->type) {
                    case 'expense':
                        // Debit Expense, Credit Asset/Liability
                        $debitAccountId = $request->to_account_id; // Category Expense chosen in form
                        $creditAccountId = $request->from_account_id; // Cash/Bank/CC
                        break;
                    case 'income':
                        // Debit Asset, Credit Income
                        $debitAccountId = $request->to_account_id; // Cash/Bank
                        $creditAccountId = $request->from_account_id; // Income Source
                        break;
                    case 'transfer':
                        // Debit Destination Asset, Credit Source Asset
                        $debitAccountId = $request->to_account_id;
                        $creditAccountId = $request->from_account_id;
                        break;
                    case 'investment':
                        // Debit Investment Asset, Credit Source Cash/Bank
                        $debitAccountId = $request->to_account_id; // Investment Account
                        $creditAccountId = $request->from_account_id; // Funding Account
                        
                        // Handle Auto-recording to Investment module
                        $this->handleInvestmentRecording($request, $workspaceId, $debitAccountId);
                        break;
                }

                // 2. Create Journal Lines
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
            });

            return redirect()->route('journals.index')->with('success', 'Transaksi berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function handleInvestmentRecording($request, $workspaceId, $accountId)
    {
        // Check if holding exists for this account & ticker
        $holding = AssetHolding::where('workspace_id', $workspaceId)
            ->where('account_id', $accountId)
            ->where('ticker', $request->ticker)
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
                'asset_name' => $request->asset_name ?? $request->description,
                'asset_type' => 'stock', // Default? Or should it be in form?
                'ticker' => $request->ticker,
                'quantity' => $request->quantity,
                'avg_buy_price' => $price,
                'current_price' => $price,
                'last_updated' => Carbon::now(),
            ]);
        }

        // Add to historical prices
        AssetPrice::create([
            'account_id' => $accountId,
            'asset_type' => 'stock',
            'ticker' => $request->ticker,
            'price' => $price,
            'price_date' => $request->date,
            'source' => 'Auto From Transaction',
        ]);
    }
}
