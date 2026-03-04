<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Budget;
use App\Models\RecurringTransaction;
use App\Models\Installment;
use App\Models\AssetHolding;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::first();
        $user = User::first();

        if (!$workspace || !$user) {
            $this->command->info('Please run WorkspaceSeeder and UserSeeder first.');
            return;
        }

        $workspaceId = $workspace->id;
        $userId = $user->id;

        // Clean up before seeding to avoid duplicates or mess
        // But keep accounts (already seeded by ChartOfAccountsSeeder)
        DB::table('journal_lines')->delete();
        DB::table('journal_entries')->delete();
        DB::table('budgets')->where('workspace_id', $workspaceId)->delete();
        DB::table('recurring_transactions')->where('workspace_id', $workspaceId)->delete();
        DB::table('installments')->where('workspace_id', $workspaceId)->delete();
        DB::table('asset_holdings')->where('workspace_id', $workspaceId)->delete();

        $bankUtama = Account::where('code', '1120')->first();
        $kasTunai = Account::where('code', '1110')->first();
        $modalAwal = Account::where('code', '3100')->first();
        $gajiSuami = Account::where('code', '4100')->first();
        $belanjaBulanan = Account::where('code', '5110')->first();
        $listrik = Account::where('code', '5120')->first();
        $internet = Account::where('code', '5140')->first();
        $kpr = Account::where('code', '2210')->first();
        $cicilanKpr = Account::where('code', '5510')->first();
        $emasFisik = Account::where('code', '1310')->first();

        // 1. Saldo Awal (Equity -> Bank)
        $this->createJournal($workspaceId, 'Saldo Awal', '2026-01-01', [
            ['account_id' => $bankUtama->id, 'debit' => 50000000, 'credit' => 0],
            ['account_id' => $kasTunai->id, 'debit' => 2000000, 'credit' => 0],
            ['account_id' => $modalAwal->id, 'debit' => 0, 'credit' => 52000000],
        ]);

        // 2. Pendapatan Gaji (Jan & Feb)
        for ($m = 1; $m <= 2; $m++) {
            $date = "2026-0$m-25";
            $this->createJournal($workspaceId, "Gaji Jan/Feb 2026", $date, [
                ['account_id' => $bankUtama->id, 'debit' => 15000000, 'credit' => 0],
                ['account_id' => $gajiSuami->id, 'debit' => 0, 'credit' => 15000000],
            ]);
        }

        // 3. Pengeluaran Rutin
        $this->createJournal($workspaceId, "Belanja Bulanan", '2026-02-05', [
            ['account_id' => $belanjaBulanan->id, 'debit' => 3000000, 'credit' => 0],
            ['account_id' => $bankUtama->id, 'debit' => 0, 'credit' => 3000000],
        ]);

        // 4. Budget untuk Maret 2026
        Budget::create([
            'workspace_id' => $workspaceId,
            'account_id' => $belanjaBulanan->id,
            'amount' => 4000000,
            'month' => 3,
            'year' => 2026
        ]);

        Budget::create([
            'workspace_id' => $workspaceId,
            'account_id' => $listrik->id,
            'amount' => 1000000,
            'month' => 3,
            'year' => 2026
        ]);

        // 5. Recurring Transaction (Tagihan Rutin)
        RecurringTransaction::create([
            'workspace_id' => $workspaceId,
            'name' => 'Tagihan Internet MyRepublic',
            'description' => 'Biaya internet bulanan',
            'amount' => 450000,
            'debit_account_id' => $internet->id,
            'credit_account_id' => $bankUtama->id,
            'frequency' => 'monthly',
            'next_due_date' => Carbon::now()->addDays(3),
            'is_active' => true
        ]);

        // 6. Installment (Cicilan KPR)
        Installment::create([
            'workspace_id' => $workspaceId,
            'name' => 'KPR Rumah Serpong',
            'account_id' => $kpr->id,
            'total_amount' => 500000000,
            'monthly_amount' => 5000000,
            'total_periods' => 180, // 15 years
            'remaining_periods' => 160,
            'start_date' => '2024-07-01',
            'interest_rate' => 8.5,
            'status' => 'active'
        ]);

        // 7. Investment Asset
        AssetHolding::create([
            'workspace_id' => $workspaceId,
            'account_id' => $emasFisik->id,
            'asset_name' => 'Emas Antam 10g',
            'asset_type' => 'gold',
            'ticker' => 'GOLD',
            'quantity' => 10,
            'avg_buy_price' => 1100000,
            'current_price' => 1250000,
            'last_updated' => Carbon::now()
        ]);
        
        $this->command->info('Sample financial data seeded successfully.');
    }

    private function createJournal($workspaceId, $desc, $date, $lines)
    {
        $journal = JournalEntry::create([
            'workspace_id' => $workspaceId,
            'date' => $date,
            'reference' => 'JRN-' . Carbon::parse($date)->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'description' => $desc,
        ]);

        foreach ($lines as $line) {
            $journal->lines()->create($line);
        }
    }
}
