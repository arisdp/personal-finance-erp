<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = session('active_workspace_id');

        // Fetch postable accounts for dropdown
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();

        // Default filters
        $accountId = $request->input('account_id', $accounts->first()->id ?? null);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $selectedAccount = Account::find($accountId);

        $beginningBalance = 0;
        $mutations = collect();
        $totalDebit = 0;
        $totalCredit = 0;

        if ($selectedAccount && $workspaceId) {
            // 1. Calculate Beginning Balance (Saldo Awal) up to start_date
            // The formula depends on the account category:
            // Asset/Expense: Normal Balance is Debit (Debit - Credit)
            // Liability/Equity/Income: Normal Balance is Credit (Credit - Debit)

            $isDebitNormal = in_array($selectedAccount->category, ['asset', 'expense']);

            $beginningQuery = JournalLine::join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->where('journal_entries.workspace_id', $workspaceId)
                ->where('journal_lines.account_id', $selectedAccount->id)
                ->whereDate('journal_entries.date', '<', $startDate)
                ->selectRaw('SUM(debit) as sum_debit, SUM(credit) as sum_credit')
                ->first();

            if ($isDebitNormal) {
                $beginningBalance = ($beginningQuery->sum_debit ?? 0) - ($beginningQuery->sum_credit ?? 0);
            } else {
                $beginningBalance = ($beginningQuery->sum_credit ?? 0) - ($beginningQuery->sum_debit ?? 0);
            }

            // 2. Fetch Mutations (Transactions) within the date range
            $mutations = JournalLine::with('journalEntry')
                ->whereHas('journalEntry', function($query) use ($workspaceId, $startDate, $endDate) {
                    $query->where('workspace_id', $workspaceId)
                          ->whereBetween('date', [$startDate, $endDate]);
                })
                ->where('account_id', $selectedAccount->id)
                // sort by date ascending, then ID 
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->orderBy('journal_entries.date', 'asc')
                ->orderBy('journal_lines.id', 'asc')
                ->select('journal_lines.*') 
                ->get();

            $totalDebit = $mutations->sum('debit');
            $totalCredit = $mutations->sum('credit');
        }

        return view('ledger.index', compact(
            'accounts',
            'selectedAccount',
            'startDate',
            'endDate',
            'beginningBalance',
            'mutations',
            'totalDebit',
            'totalCredit'
        ));
    }
}
