<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@erp.com')->first();
        $workspace = Workspace::where('slug', 'keluarga-utama')->first();

        if (!$admin || !$workspace) return;

        // 1. Get relevant accounts
        $kas = Account::where('code', '1101')->first(); // Kas Utama
        $bank = Account::where('code', '1102')->first(); // Bank BCA
        $cc = Account::where('code', '2101')->first(); // Kartu Kredit (Must have track_limit=true)
        $income = Account::where('code', '4101')->first(); // Gaji Bulanan
        $expense = Account::where('code', '5101')->first(); // Makan & Minum
        $investment = Account::where('code', '1301')->first(); // Emas / Portfolio

        if (!$kas || !$income || !$expense) return;

        // Ensure CC has tracking
        if ($cc) {
            $cc->update(['track_limit' => true, 'credit_limit' => 10000000]);
        }

        // 2. Clear existing journals for clean test if needed
        // JournalEntry::where('workspace_id', $workspace->id)->delete();

        // Transaction 1: Gaji (Income)
        $this->createJournal($workspace, $admin, 'Gaji Januari', '2026-01-25', [
            ['account_id' => $bank->id, 'debit' => 15000000, 'credit' => 0],
            ['account_id' => $income->id, 'debit' => 0, 'credit' => 15000000],
        ]);

        // Transaction 2: Makan (Expense) - Cash
        $this->createJournal($workspace, $admin, 'Makan Siang Bakso', '2026-02-01', [
            ['account_id' => $expense->id, 'debit' => 50000, 'credit' => 0],
            ['account_id' => $kas->id, 'debit' => 0, 'credit' => 50000],
        ]);

        // Transaction 3: Belanja Bulanan (Expense) - Credit Card
        if ($cc) {
            $this->createJournal($workspace, $admin, 'Supermarket Bulanan', '2026-02-05', [
                ['account_id' => $expense->id, 'debit' => 1200000, 'credit' => 0],
                ['account_id' => $cc->id, 'debit' => 0, 'credit' => 1200000],
            ]);
        }

        // Transaction 4: Investasi (Transfer)
        if ($investment) {
            $this->createJournal($workspace, $admin, 'Beli Emas Antam', '2026-02-10', [
                ['account_id' => $investment->id, 'debit' => 1000000, 'credit' => 0],
                ['account_id' => $bank->id, 'debit' => 0, 'credit' => 1000000],
            ]);
        }

        // Transaction 5: Bayar Kartu Kredit
        if ($cc) {
            $this->createJournal($workspace, $admin, 'Pembayaran Tagihan CC', '2026-02-20', [
                ['account_id' => $cc->id, 'debit' => 500000, 'credit' => 0],
                ['account_id' => $bank->id, 'debit' => 0, 'credit' => 500000],
            ]);
        }
    }

    private function createJournal($workspace, $user, $desc, $date, $lines)
    {
        $entry = JournalEntry::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'date' => $date,
            'description' => $desc,
            'created_by' => $user->id,
        ]);

        foreach ($lines as $line) {
            JournalLine::create([
                'id' => (string) Str::uuid(),
                'journal_entry_id' => $entry->id,
                'account_id' => $line['account_id'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ]);
        }
    }
}
