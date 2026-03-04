<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $asOfDate = $request->input('date', Carbon::now()->format('Y-m-d'));

        // Fetch top-level accounts
        $accounts = Account::whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $reportData = $this->buildHierarchicalTrialBalance($accounts, $workspaceId, $asOfDate);

        // Calculate Totals from top-level accounts only to avoid double counting
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $account->calculateTotalBalance($workspaceId, $asOfDate);
            if (in_array($account->category, ['asset', 'expense'])) {
                if ($balance >= 0) $totalDebit += $balance;
                else $totalCredit += abs($balance);
            } else {
                if ($balance >= 0) $totalCredit += $balance;
                else $totalDebit += abs($balance);
            }
        }

        return view('reports.trial_balance', compact('reportData', 'asOfDate', 'totalDebit', 'totalCredit'));
    }

    private function buildHierarchicalTrialBalance($accounts, $workspaceId, $asOfDate, $level = 0)
    {
        $data = [];

        foreach ($accounts as $account) {
            $totalBalance = $account->calculateTotalBalance($workspaceId, $asOfDate);

            // Skip if no balance and no activity in current month
            if ($totalBalance == 0 && $account->calculateBalance($workspaceId, $asOfDate) == 0) {
                if ($account->children->isEmpty()) continue;
            }

            $debitBalance = 0;
            $creditBalance = 0;

            if (in_array($account->category, ['asset', 'expense'])) {
                if ($totalBalance >= 0) $debitBalance = $totalBalance;
                else $creditBalance = abs($totalBalance);
            } else {
                if ($totalBalance >= 0) $creditBalance = $totalBalance;
                else $debitBalance = abs($totalBalance);
            }

            $data[] = [
                'code' => $account->code,
                'name' => $account->name,
                'category' => $account->category,
                'debit' => $debitBalance,
                'credit' => $creditBalance,
                'level' => $level,
                'is_postable' => $account->is_postable
            ];

            if ($account->children->isNotEmpty()) {
                $childrenData = $this->buildHierarchicalTrialBalance($account->children()->orderBy('code')->get(), $workspaceId, $asOfDate, $level + 1);
                $data = array_merge($data, $childrenData);
            }
        }

        return $data;
    }

    /**
     * Profit and Loss Report
     */
    public function profitAndLoss(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get top-level Income accounts
        $incomeRoots = Account::where('category', 'income')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();
            
        // Get top-level Expense accounts
        $expenseRoots = Account::where('category', 'expense')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $incomeData = [];
        $totalIncome = 0;
        foreach ($incomeRoots as $root) {
            $this->buildHierarchicalPL($root, $workspaceId, $startDate, $endDate, 0, $incomeData);
            $totalIncome += $root->calculateTotalBalance($workspaceId, $startDate, $endDate);
        }

        $expenseData = [];
        $totalExpense = 0;
        foreach ($expenseRoots as $root) {
            $this->buildHierarchicalPL($root, $workspaceId, $startDate, $endDate, 0, $expenseData);
            $totalExpense += $root->calculateTotalBalance($workspaceId, $startDate, $endDate);
        }

        $netProfit = $totalIncome - $totalExpense;

        return view('reports.profit_loss', compact(
            'incomeData', 
            'expenseData', 
            'totalIncome', 
            'totalExpense', 
            'netProfit',
            'startDate',
            'endDate'
        ));
    }

    private function buildHierarchicalPL($account, $workspaceId, $startDate, $endDate, $level, &$dataArray)
    {
        $balance = $account->calculateTotalBalance($workspaceId, $startDate, $endDate);
        $displayBalance = abs($balance);

        if ($displayBalance != 0) {
            $dataArray[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'amount' => $displayBalance,
                'level' => $level,
                'is_parent' => $account->children()->count() > 0
            ];

            foreach ($account->children()->orderBy('code')->get() as $child) {
                $this->buildHierarchicalPL($child, $workspaceId, $startDate, $endDate, $level + 1, $dataArray);
            }
        }
    }

    /**
     * Trial Balance PDF
     */
    public function trialBalancePdf(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $asOfDate = $request->input('date', Carbon::now()->format('Y-m-d'));

        $accounts = Account::whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $reportData = $this->buildHierarchicalTrialBalance($accounts, $workspaceId, $asOfDate);

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($accounts as $account) {
            $balance = $account->calculateTotalBalance($workspaceId, $asOfDate);
            if (in_array($account->category, ['asset', 'expense'])) {
                if ($balance >= 0) $totalDebit += $balance;
                else $totalCredit += abs($balance);
            } else {
                if ($balance >= 0) $totalCredit += $balance;
                else $totalDebit += abs($balance);
            }
        }

        $pdf = Pdf::loadView('pdf.trial_balance', compact('reportData', 'asOfDate', 'totalDebit', 'totalCredit'));
        return $pdf->download("Trial-Balance-{$asOfDate}.pdf");
    }

    /**
     * Profit and Loss PDF
     */
    public function profitAndLossPdf(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $incomeRoots = Account::where('category', 'income')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();
            
        $expenseRoots = Account::where('category', 'expense')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $incomeData = [];
        $totalIncome = 0;
        foreach ($incomeRoots as $root) {
            $this->buildHierarchicalPL($root, $workspaceId, $startDate, $endDate, 0, $incomeData);
            $totalIncome += $root->calculateTotalBalance($workspaceId, $startDate, $endDate);
        }

        $expenseData = [];
        $totalExpense = 0;
        foreach ($expenseRoots as $root) {
            $this->buildHierarchicalPL($root, $workspaceId, $startDate, $endDate, 0, $expenseData);
            $totalExpense += $root->calculateTotalBalance($workspaceId, $startDate, $endDate);
        }

        $netProfit = $totalIncome - $totalExpense;

        $pdf = Pdf::loadView('pdf.profit_loss', compact(
            'incomeData', 
            'expenseData', 
            'totalIncome', 
            'totalExpense', 
            'netProfit',
            'startDate',
            'endDate'
        ));

        return $pdf->download("Profit-Loss-{$startDate}-to-{$endDate}.pdf");
    }
}
