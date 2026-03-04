<?php

namespace App\Http\Controllers;

use App\Services\FinancialSummaryService;

class DashboardController extends Controller
{
    public function index(FinancialSummaryService $financialService)
    {
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
            'installmentSummary'
        ));
    }
}
