<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $assets = $this->getBalanceByType('asset');
        $liabilities = $this->getBalanceByType('liability');
        $equity = $this->getBalanceByType('equity');
        $income = $this->getBalanceByType('income');
        $expense = $this->getBalanceByType('expense');

        $netIncome = $income - $expense;

        return view('dashboard.index', compact(
            'assets',
            'liabilities',
            'equity',
            'netIncome'
        ));
    }

    private function getBalanceByType($type)
    {
        return JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('accounts.type', $type)
            ->where('accounts.user_id', auth()->id())
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;
    }
}
