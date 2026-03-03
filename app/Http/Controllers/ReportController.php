<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $asOfDate = $request->input('date', Carbon::now()->format('Y-m-d'));

        // Fetch all postable accounts
        $accounts = Account::where('is_postable', true)
            ->with(['journalLines' => function($query) use ($workspaceId, $asOfDate) {
                $query->whereHas('journalEntry', function($q) use ($workspaceId, $asOfDate) {
                    $q->where('workspace_id', $workspaceId)
                      ->where('date', '<=', $asOfDate);
                });
            }])
            ->orderBy('code')
            ->get();

        $reportData = $accounts->map(function($account) {
            $sumDebit = $account->journalLines->sum('debit');
            $sumCredit = $account->journalLines->sum('credit');
            
            $debitBalance = 0;
            $creditBalance = 0;

            // Logic for Normal Balance
            // Asset/Expense normally Debit
            if (in_array($account->category, ['asset', 'expense'])) {
                $balance = $sumDebit - $sumCredit;
                if ($balance >= 0) $debitBalance = $balance;
                else $creditBalance = abs($balance);
            } else {
                // Liability/Equity/Income normally Credit
                $balance = $sumCredit - $sumDebit;
                if ($balance >= 0) $creditBalance = $balance;
                else $debitBalance = abs($balance);
            }

            return [
                'code' => $account->code,
                'name' => $account->name,
                'category' => $account->category,
                'debit' => $debitBalance,
                'credit' => $creditBalance
            ];
        })->filter(function($row) {
            return $row['debit'] > 0 || $row['credit'] > 0;
        });

        $totalDebit = $reportData->sum('debit');
        $totalCredit = $reportData->sum('credit');

        return view('reports.trial_balance', compact('reportData', 'asOfDate', 'totalDebit', 'totalCredit'));
    }

    /**
     * Profit and Loss Report
     */
    public function profitAndLoss(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get Income Accounts
        $incomeAccounts = Account::where('category', 'income')
            ->orderBy('code')
            ->get()
            ->map(function($account) use ($workspaceId, $startDate, $endDate) {
                $amount = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($workspaceId, $startDate, $endDate) {
                        $q->where('workspace_id', $workspaceId)
                          ->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->selectRaw('SUM(credit - debit) as total')
                    ->value('total') ?? 0;
                
                return ['name' => $account->name, 'code' => $account->code, 'amount' => (float)$amount];
            })->filter(fn($a) => $a['amount'] != 0);

        // Get Expense Accounts
        $expenseAccounts = Account::where('category', 'expense')
            ->orderBy('code')
            ->get()
            ->map(function($account) use ($workspaceId, $startDate, $endDate) {
                $amount = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($workspaceId, $startDate, $endDate) {
                        $q->where('workspace_id', $workspaceId)
                          ->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->selectRaw('SUM(debit - credit) as total')
                    ->value('total') ?? 0;
                
                return ['name' => $account->name, 'code' => $account->code, 'amount' => (float)$amount];
            })->filter(fn($a) => $a['amount'] != 0);

        $totalIncome = $incomeAccounts->sum('amount');
        $totalExpense = $expenseAccounts->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        return view('reports.profit_loss', compact(
            'incomeAccounts', 
            'expenseAccounts', 
            'totalIncome', 
            'totalExpense', 
            'netProfit',
            'startDate',
            'endDate'
        ));
    }

    public function trialBalancePdf(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $asOfDate = $request->input('date', Carbon::now()->format('Y-m-d'));

        $accounts = Account::where('is_postable', true)
            ->with(['journalLines' => function($query) use ($workspaceId, $asOfDate) {
                $query->whereHas('journalEntry', function($q) use ($workspaceId, $asOfDate) {
                    $q->where('workspace_id', $workspaceId)
                      ->where('date', '<=', $asOfDate);
                });
            }])
            ->orderBy('code')
            ->get();

        $reportData = $accounts->map(function($account) {
            $sumDebit = $account->journalLines->sum('debit');
            $sumCredit = $account->journalLines->sum('credit');
            $debitBalance = 0;
            $creditBalance = 0;

            if (in_array($account->category, ['asset', 'expense'])) {
                $balance = $sumDebit - $sumCredit;
                if ($balance >= 0) $debitBalance = $balance;
                else $creditBalance = abs($balance);
            } else {
                $balance = $sumCredit - $sumDebit;
                if ($balance >= 0) $creditBalance = $balance;
                else $debitBalance = abs($balance);
            }

            return ['code' => $account->code, 'name' => $account->name, 'debit' => $debitBalance, 'credit' => $creditBalance];
        })->filter(fn($row) => $row['debit'] > 0 || $row['credit'] > 0);

        $totalDebit = $reportData->sum('debit');
        $totalCredit = $reportData->sum('credit');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.trial_balance', compact('reportData', 'asOfDate', 'totalDebit', 'totalCredit'));
        return $pdf->download("Trial-Balance-{$asOfDate}.pdf");
    }

    public function profitAndLossPdf(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $incomeAccounts = Account::where('category', 'income')->orderBy('code')->get()
            ->map(function($account) use ($workspaceId, $startDate, $endDate) {
                $amount = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($workspaceId, $startDate, $endDate) {
                        $q->where('workspace_id', $workspaceId)->whereBetween('date', [$startDate, $endDate]);
                    })->selectRaw('SUM(credit - debit) as total')->value('total') ?? 0;
                return ['name' => $account->name, 'amount' => (float)$amount];
            })->filter(fn($a) => $a['amount'] != 0);

        $expenseAccounts = Account::where('category', 'expense')->orderBy('code')->get()
            ->map(function($account) use ($workspaceId, $startDate, $endDate) {
                $amount = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($workspaceId, $startDate, $endDate) {
                        $q->where('workspace_id', $workspaceId)->whereBetween('date', [$startDate, $endDate]);
                    })->selectRaw('SUM(debit - credit) as total')->value('total') ?? 0;
                return ['name' => $account->name, 'amount' => (float)$amount];
            })->filter(fn($a) => $a['amount'] != 0);

        $totalIncome = $incomeAccounts->sum('amount');
        $totalExpense = $expenseAccounts->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.profit_loss', compact('incomeAccounts', 'expenseAccounts', 'totalIncome', 'totalExpense', 'netProfit', 'startDate', 'endDate'));
        return $pdf->download("Profit-Loss-{$startDate}-to-{$endDate}.pdf");
    }
}
