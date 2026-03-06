<?php

namespace App\Http\Controllers;

use App\Services\FinancialSummaryService;
use App\Models\AssetHolding;

class DashboardController extends Controller
{
    public function index(FinancialSummaryService $financialService)
    {
        $workspaceId = session('active_workspace_id');

        // 1. Get Top Level Metrics
        $totalCash = $financialService->getTotalCash();
        $totalInvestment = $financialService->getTotalInvestment();
        $totalDebt = $financialService->getTotalDebt();
        $netWorth = $financialService->getNetWorth();

        // 2. Get Cashflow Data
        $cashflowThisMonth = $financialService->getMonthlyCashflow();
        
        // 3. Get Emergency Fund Status
        $emergencyFund = $financialService->getEmergencyFundStatus();

        // 4. Get Credit Card Info
        $creditCards = $financialService->getCreditLimitSummary();

        // 5. Get Budget Summary
        $budgetSummary = $financialService->getBudgetSummary();

        // 6. Get Upcoming Bills
        $upcomingBills = $financialService->getUpcomingBills();

        // 7. Get Installment Summary
        $installmentSummary = $financialService->getInstallmentSummary();

        // 8. Investment Portfolio Summary
        $holdings = AssetHolding::with('instrument')
            ->where('workspace_id', $workspaceId)
            ->get();

        $investmentSummary = [
            'total_cost'         => $holdings->sum('cost_basis'),
            'total_market_value' => $holdings->sum('market_value'),
            'total_gain_loss'    => $holdings->sum('unrealized_gain_loss'),
            'count'              => $holdings->count(),
        ];
        $investmentSummary['gain_loss_pct'] = $investmentSummary['total_cost'] > 0
            ? round(($investmentSummary['total_gain_loss'] / $investmentSummary['total_cost']) * 100, 2)
            : 0;

        // Top 5 holdings by market value
        $topHoldings = $holdings->sortByDesc('market_value')->take(5);

        return view('dashboard.index', compact(
            'totalCash',
            'totalInvestment',
            'totalDebt',
            'netWorth',
            'cashflowThisMonth',
            'emergencyFund',
            'creditCards',
            'budgetSummary',
            'upcomingBills',
            'installmentSummary',
            'investmentSummary',
            'topHoldings'
        ));
    }
}

