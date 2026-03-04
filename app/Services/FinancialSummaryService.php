<?php

namespace App\Services;

use App\Models\Workspace;
use App\Models\JournalLine;
use App\Models\AssetHolding;
use App\Models\Installment;
use App\Models\HealthConfig;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialSummaryService
{
    protected $workspaceId;
    protected $workspace;

    public function __construct(?string $workspaceId = null)
    {
        $this->workspaceId = $workspaceId ?? session('active_workspace_id');
        $this->workspace = Workspace::find($this->workspaceId);
    }

    /**
     * Set workspace context dynamically
     */
    public function setWorkspace(string $workspaceId): self
    {
        $this->workspaceId = $workspaceId;
        $this->workspace = Workspace::find($workspaceId);
        return $this;
    }

    /**
     * Get Total Cash (All liquid assets)
     */
    public function getTotalCash(): float
    {
        if (!$this->workspaceId) return 0;

        return JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'asset')
            ->whereIn('accounts.type', ['bank', 'ewallet', 'cash'])
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;
    }

    /**
     * Get Total Investment (Asset category, investment type + Asset Holdings)
     */
    public function getTotalInvestment(): float
    {
        if (!$this->workspaceId) return 0;

        // 1. Get cash balance in investment accounts
        $cashInInvestments = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'asset')
            ->where('accounts.type', 'investment')
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        // 2. Get current market value of physical/digital assets from AssetHoldings
        $holdingsValue = AssetHolding::where('workspace_id', $this->workspaceId)
            ->get()
            ->sum('market_value');

        return $cashInInvestments + $holdingsValue;
    }

    /**
     * Get Total Debt (All Liability accounts)
     */
    public function getTotalDebt(): float
    {
        if (!$this->workspaceId) return 0;

        return JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'liability')
            ->selectRaw('SUM(credit - debit) as balance')
            ->value('balance') ?? 0;
    }

    /**
     * Get Credit Cards/Paylater Usage & Limits
     */
    public function getCreditLimitSummary(): array
    {
        if (!$this->workspaceId) return [];

        $cards = \App\Models\Account::where('category', 'liability')
            ->where('track_limit', true)
            ->get();

        return $cards->map(function($card) {
            return [
                'name' => $card->name,
                'limit' => (float)$card->credit_limit,
                'used' => (float)$card->used_limit, // This uses the accessor in Account model
                'available' => (float)$card->available_limit,
                'usage_percent' => $card->credit_limit > 0 ? min(100, round(($card->used_limit / $card->credit_limit) * 100)) : 0
            ];
        })->toArray();
    }

    /**
     * Get Total Assets
     */
    public function getTotalAssets(): float
    {
        if (!$this->workspaceId) return 0;

        $cashAssets = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'asset')
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        $holdingsValue = AssetHolding::where('workspace_id', $this->workspaceId)
            ->get()
            ->sum('market_value');

        return $cashAssets + $holdingsValue;
    }

    /**
     * Get Net Worth (Assets - Liabilities)
     */
    public function getNetWorth(): float
    {
        return $this->getTotalAssets() - $this->getTotalDebt();
    }

    /**
     * Get Budget vs Actual Summary
     */
    public function getBudgetSummary(): array
    {
        if (!$this->workspaceId) return [];

        $month = (int)date('n');
        $year = (int)date('Y');

        $budgets = \App\Models\Budget::where('workspace_id', $this->workspaceId)
            ->where('month', $month)
            ->where('year', $year)
            ->with('account')
            ->get();

        return $budgets->map(function($budget) use ($month, $year) {
            $actual = JournalLine::join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
                ->where('journal_entries.workspace_id', $this->workspaceId)
                ->where('journal_lines.account_id', $budget->account_id)
                ->whereMonth('journal_entries.date', $month)
                ->whereYear('journal_entries.date', $year)
                ->selectRaw('SUM(debit - credit) as total')
                ->value('total') ?? 0;

            $percent = $budget->amount > 0 ? round(($actual / $budget->amount) * 100) : 0;
            
            return [
                'name' => $budget->account->name,
                'budget' => (float)$budget->amount,
                'actual' => (float)$actual,
                'percent' => (float)$percent,
                'remaining' => (float)($budget->amount - $actual)
            ];
        })->toArray();
    }

    /**
     * Get Upcoming Bills (from Recurring Transactions)
     */
    public function getUpcomingBills(): array
    {
        if (!$this->workspaceId) return [];

        return \App\Models\RecurringTransaction::where('workspace_id', $this->workspaceId)
            ->where('is_active', true)
            ->where('next_due_date', '<=', now()->addDays(7))
            ->with(['debitAccount', 'creditAccount'])
            ->orderBy('next_due_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get Installment Summary
     */
    public function getInstallmentSummary(): array
    {
        if (!$this->workspaceId) {
            return [
                'total_monthly' => 0.0,
                'total_remaining' => 0.0,
                'count' => 0
            ];
        }

        $activeInstallments = \App\Models\Installment::where('workspace_id', $this->workspaceId)
            ->where('status', 'active')
            ->get();

        return [
            'total_monthly' => (float)$activeInstallments->sum('monthly_amount'),
            'total_remaining' => (float)$activeInstallments->sum(fn($i) => $i->remaining_amount),
            'count' => $activeInstallments->count()
        ];
    }

    /**
     * Get Monthly Cashflow (Income & Expense)
     */
    public function getMonthlyCashflow(int $month = null, int $year = null): array
    {
        if (!$this->workspaceId) return ['income' => 0, 'expense' => 0, 'net' => 0];

        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $income = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'income')
            ->whereMonth('journal_entries.date', $month)
            ->whereYear('journal_entries.date', $year)
            ->selectRaw('SUM(credit - debit) as balance')
            ->value('balance') ?? 0;

        $expense = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'expense')
            ->whereMonth('journal_entries.date', $month)
            ->whereYear('journal_entries.date', $year)
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'net' => (float) ($income - $expense)
        ];
    }

    /**
     * Get Monthly Average Expense (Last 3 months)
     */
    public function getAverageMonthlyExpense(): float
    {
        if (!$this->workspaceId) return 0;

        $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $totalExpense = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'expense')
            ->whereBetween('journal_entries.date', [$threeMonthsAgo, $endOfLastMonth])
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        return $totalExpense / 3;
    }

    /**
     * Get Emergency Fund Status
     */
    public function getEmergencyFundStatus(): array
    {
        if (!$this->workspaceId) return [
            'total_fund' => 0,
            'avg_monthly_expense' => 0,
            'current_months' => 0,
            'target_months' => 6,
            'target_amount' => 0,
            'status' => 'danger',
            'progress_percent' => 0
        ];

        $config = HealthConfig::where('workspace_id', $this->workspaceId)
            ->where('metric_key', 'emergency_fund')
            ->first();

        $targetMonths = $config ? $config->target_value : 6;
        
        // Dana Darurat (Account 1200)
        $emergencyFundCash = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'asset')
            ->where('accounts.code', 'like', '12%') // 1200 Emergency Fund
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        $avgExpense = $this->getAverageMonthlyExpense();
        
        // If no expenses recorded yet, assume 5,000,000 default for calculation to avoid division by zero
        if ($avgExpense <= 0) $avgExpense = 5000000;

        $currentCoverageMonths = round($emergencyFundCash / $avgExpense, 1);

        $status = 'danger';
        if ($currentCoverageMonths >= $targetMonths) {
            $status = 'healthy';
        } else if ($currentCoverageMonths >= ($targetMonths * 0.5)) {
            $status = 'warning';
        }

        return [
            'total_fund' => (float) $emergencyFundCash,
            'avg_monthly_expense' => (float) $avgExpense,
            'current_months' => $currentCoverageMonths,
            'target_months' => (float) $targetMonths,
            'target_amount' => $targetMonths * $avgExpense,
            'status' => $status,
            'progress_percent' => min(100, round(($currentCoverageMonths / $targetMonths) * 100))
        ];
    }
}
