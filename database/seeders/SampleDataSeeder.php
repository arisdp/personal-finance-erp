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
        $makanDiLuar = Account::where('code', '5410')->first();
        
        $kpr = Account::where('code', '2210')->first();
        $cicilanKpr = Account::where('code', '5510')->first();
        
        $emasFisik = Account::where('code', '1310')->first();
        
        // Liability Cards
        $ccUtama = Account::where('code', '2110')->first(); // Kartu Kredit Utama (Limit 20jt)
        $paylater = Account::where('code', '2130')->first(); // Shopee Paylater (Limit 5jt)

        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        
        $lastMonth = Carbon::now()->subMonth();

        // 1. Saldo Awal (Equity -> Bank)
        $this->createJournal($workspaceId, 'Saldo Awal', $now->copy()->startOfYear()->format('Y-m-d'), [
            ['account_id' => $bankUtama->id, 'debit' => 50000000, 'credit' => 0],
            ['account_id' => $kasTunai->id, 'debit' => 2000000, 'credit' => 0],
            ['account_id' => $modalAwal->id, 'debit' => 0, 'credit' => 52000000],
        ]);

        // 2. Pendapatan Gaji (Bulan Lalu & Bulan Ini)
        $this->createJournal($workspaceId, "Gaji Bulan Lalu", $lastMonth->copy()->endOfMonth()->format('Y-m-d'), [
            ['account_id' => $bankUtama->id, 'debit' => 15000000, 'credit' => 0],
            ['account_id' => $gajiSuami->id, 'debit' => 0, 'credit' => 15000000],
        ]);
        
        $this->createJournal($workspaceId, "Gaji Bulan Ini", $now->copy()->startOfMonth()->addDays(1)->format('Y-m-d'), [
            ['account_id' => $bankUtama->id, 'debit' => 15000000, 'credit' => 0],
            ['account_id' => $gajiSuami->id, 'debit' => 0, 'credit' => 15000000],
        ]);

        // 3. Pengeluaran Rutin Bulan Ini (Cash/Bank)
        $this->createJournal($workspaceId, "Belanja Bulanan Supermarket", $now->copy()->startOfMonth()->addDays(2)->format('Y-m-d'), [
            ['account_id' => $belanjaBulanan->id, 'debit' => 3000000, 'credit' => 0],
            ['account_id' => $bankUtama->id, 'debit' => 0, 'credit' => 3000000],
        ]);

        // 4. Simulasi Penggunaan Kartu Kredit & Paylater (Hutang)
        // Kartu Kredit terpakai 5.000.000 untuk beli HP/Elektronik (Expense)
        $this->createJournal($workspaceId, "Beli Gadget dgn Kartu Kredit", $now->copy()->subDays(5)->format('Y-m-d'), [
            ['account_id' => Account::where('code', '5440')->first()->id, 'debit' => 5000000, 'credit' => 0], // Belanja Pribadi
            ['account_id' => $ccUtama->id, 'debit' => 0, 'credit' => 5000000], // Kartu Kredit Utama
        ]);
        
        // Paylater terpakai 1.500.000 untuk Makan di Luar
        $this->createJournal($workspaceId, "Traktir Keluarga (Paylater)", $now->copy()->subDays(2)->format('Y-m-d'), [
            ['account_id' => $makanDiLuar->id, 'debit' => 1500000, 'credit' => 0],
            ['account_id' => $paylater->id, 'debit' => 0, 'credit' => 1500000],
        ]);

        // 5. Budget untuk Bulan Ini
        Budget::create([
            'workspace_id' => $workspaceId,
            'account_id' => $belanjaBulanan->id,
            'amount' => 4000000,
            'month' => $currentMonth,
            'year' => $currentYear
        ]);

        Budget::create([
            'workspace_id' => $workspaceId,
            'account_id' => $makanDiLuar->id,
            'amount' => 2000000,
            'month' => $currentMonth,
            'year' => $currentYear
        ]);

        // 6. Recurring Transaction (Tagihan Rutin)
        RecurringTransaction::create([
            'workspace_id' => $workspaceId,
            'name' => 'Tagihan Internet MyRepublic',
            'description' => 'Biaya internet bulanan',
            'amount' => 450000,
            'debit_account_id' => $internet->id,
            'credit_account_id' => $ccUtama->id, // Bayar otomatis pakai CC
            'frequency' => 'monthly',
            'next_due_date' => Carbon::now()->addDays(3),
            'is_active' => true
        ]);

        // 7. Installment (Cicilan KPR)
        Installment::create([
            'workspace_id' => $workspaceId,
            'name' => 'KPR Rumah Serpong',
            'account_id' => $kpr->id,
            'total_amount' => 500000000,
            'monthly_amount' => 5000000,
            'total_periods' => 180, // 15 years
            'remaining_periods' => 160,
            'start_date' => Carbon::now()->subMonths(20)->format('Y-m-d'),
            'interest_rate' => 8.5,
            'status' => 'active'
        ]);

        // 8. Investment Asset
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

        $rumah = Account::where('code', '1410')->first(); // Rumah (Fixed Asset)
        $kprLiability = Account::where('code', '2210')->first(); // KPR Liability

        // 9. Initial Real Estate & KPR Recognition
        if ($rumah && $kprLiability) {
            $this->createJournal($workspaceId, "Pencatatan Beli Rumah KPR", Carbon::now()->subMonths(20)->format('Y-m-d'), [
                ['account_id' => $rumah->id, 'debit' => 500000000, 'credit' => 0],
                ['account_id' => $kprLiability->id, 'debit' => 0, 'credit' => 500000000],
            ]);
        }

        // 10. Initial Gold Investment Recognition
        $this->createJournal($workspaceId, "Pembukaan Aset Investasi Emas", Carbon::now()->subMonths(6)->format('Y-m-d'), [
            ['account_id' => $emasFisik->id, 'debit' => 11000000, 'credit' => 0],
            ['account_id' => $bankUtama->id, 'debit' => 0, 'credit' => 11000000],
        ]);
        
        $this->command->info('Sample financial data (including CC and Paylater) seeded successfully.');
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
