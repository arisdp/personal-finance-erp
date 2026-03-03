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
     * Get Total Cash (Accounts 1100 - Current Assets)
     */
    public function getTotalCash(): float
    {
        if (!$this->workspaceId) return 0;

        return JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'asset')
            // Using LIKE on code to match 11% (Current Assets)
            ->where('accounts.code', 'like', '11%')
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;
    }

    /**
     * Get Total Investment (Accounts 1300 + Asset Holdings)
     */
    public function getTotalInvestment(): float
    {
        if (!$this->workspaceId) return 0;

        // 1. Get cash balance in investment accounts (1300)
        $cashInInvestments = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'asset')
            ->where('accounts.code', 'like', '13%')
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        // 2. Get current market value of physical/digital assets from AssetHoldings
        $holdingsValue = AssetHolding::where('workspace_id', $this->workspaceId)
            ->get()
            ->sum('market_value');

        return $cashInInvestments + $holdingsValue;
    }

    /**
     * Get Total Debt (Accounts 2000 - Liabilities)
     */
    public function getTotalDebt(): float
    {
        if (!$this->workspaceId) return 0;

        // Balance of all liability accounts
        return JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->where('accounts.category', 'liability')
            ->selectRaw('SUM(credit - debit) as balance') // Liabilities increase with credit
            ->value('balance') ?? 0;
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
