<?php

namespace App\Services;

use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectionService
{
    protected $workspaceId;

    public function __construct($workspaceId)
    {
        $this->workspaceId = $workspaceId;
    }

    /**
     * Calculate Projected Net Worth
     */
    public function calculate(float $annualReturnRate = 7, float $annualInflation = 3, int $years = 10): array
    {
        if (!$this->workspaceId) return [];

        // 1. Get Current Net Worth
        $summary = new FinancialSummaryService($this->workspaceId);
        $currentNetWorth = $summary->getNetWorth();

        // 2. Get Average Monthly Savings (last 6 months or all available)
        $avgSavings = $this->getAverageMonthlySavings(6);

        $projections = [];
        $runningNetWorth = $currentNetWorth;
        $monthlySavings = $avgSavings;
        
        $monthlyReturn = ($annualReturnRate / 100) / 12;
        $monthlyInflation = ($annualInflation / 100) / 12;

        $projections[] = [
            'period' => 'Current',
            'date' => Carbon::now()->format('M Y'),
            'net_worth' => round($runningNetWorth, 2),
            'savings' => round($monthlySavings, 2)
        ];

        for ($m = 1; $m <= ($years * 12); $m++) {
            // Apply return to existing capital
            $runningNetWorth *= (1 + $monthlyReturn);
            
            // Add new savings
            $runningNetWorth += $monthlySavings;

            // Adjust savings for inflation/cost of living (optional, let's keep it simple for now or adjust annually)
            if ($m % 12 === 0) {
                // monthlySavings *= (1 + ($annualInflation / 100));
                
                // Record annual snapshots
                $projections[] = [
                    'period' => 'Year ' . ($m / 12),
                    'date' => Carbon::now()->addMonths($m)->format('M Y'),
                    'net_worth' => round($runningNetWorth, 2),
                    'savings' => round($monthlySavings, 2)
                ];
            }
        }

        return $projections;
    }

    private function getAverageMonthlySavings(int $limitMonths = 6): float
    {
        $driver = DB::getDriverName();
        $dateFunc = $driver === 'pgsql' 
            ? "TO_CHAR(journal_entries.date, 'YYYY-MM')" 
            : "DATE_FORMAT(journal_entries.date, '%Y-%m')";

        $monthlyData = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.workspace_id', $this->workspaceId)
            ->whereIn('accounts.category', ['income', 'expense'])
            ->selectRaw("
                {$dateFunc} as month,
                SUM(CASE WHEN accounts.category = 'income' THEN (credit - debit) ELSE -(debit - credit) END) as savings
            ")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit($limitMonths)
            ->get();

        if ($monthlyData->isEmpty()) return 0;

        return $monthlyData->avg('savings');
    }
}
