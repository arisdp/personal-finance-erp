<?php

namespace App\Http\Controllers;

use App\Services\FinancialSummaryService;

class DashboardController extends Controller
{
    protected $financialService;

    public function __construct(FinancialSummaryService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index()
    {
        // 1. Get Top Level Metrics
        $totalCash = $this->financialService->getTotalCash();
        $totalInvestment = $this->financialService->getTotalInvestment();
        $totalDebt = $this->financialService->getTotalDebt();
        $netWorth = $this->financialService->getNetWorth();

        // 2. Get Cashflow Data
        $cashflowThisMonth = $this->financialService->getMonthlyCashflow();
        
        // 3. Get Emergency Fund Status
        $emergencyFund = $this->financialService->getEmergencyFundStatus();

        // 4. Get Credit Card Info
        $creditCards = $this->financialService->getCreditLimitSummary();

        // 5. Get Budget Summary
        $budgetSummary = $this->financialService->getBudgetSummary();

        // 6. Get Upcoming Bills
        $upcomingBills = $this->financialService->getUpcomingBills();

        // 7. Get Installment Summary
        $installmentSummary = $this->financialService->getInstallmentSummary();

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
