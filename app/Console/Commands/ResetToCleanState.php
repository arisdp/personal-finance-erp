<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetToCleanState extends Command
{
    protected $signature   = 'app:reset-clean {--force : Skip confirmation prompt}';
    protected $description = 'Hapus semua data transaksi (journal, budget, dll) dan reset kredit kartu ke 0. COA, user, workspace TIDAK akan diubah.';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $this->warn('⚠️  Peringahan: Tindakan ini akan menghapus SEMUA jurnal, budget, cicilan, investasi, dan saldo kredit.');
            $this->warn('    Data COA (Chart of Accounts), User, dan Workspace TIDAK akan dihapus.');

            if (!$this->confirm('Lanjutkan?', false)) {
                $this->info('Dibatalkan.');
                return self::FAILURE;
            }
        }

        $this->info('🧹 Memulai reset data...');

        // Hapus dalam urutan yang benar (anak dulu sebelum induk) agar tidak melanggar foreign key
        // PostgreSQL tidak mendukung SET FOREIGN_KEY_CHECKS — kita urut manual

        // 1. journal_lines (anak dari journal_entries)
        $lines = DB::table('journal_lines')->delete();
        $this->line("   ✓ journal_lines     : {$lines} baris dihapus.");

        // 2. journal_entries
        $entries = DB::table('journal_entries')->delete();
        $this->line("   ✓ journal_entries   : {$entries} baris dihapus.");

        // 3. budget
        $budgets = DB::table('budgets')->delete();
        $this->line("   ✓ budgets           : {$budgets} baris dihapus.");

        // 4. transaksi berulang
        $recurring = DB::table('recurring_transactions')->delete();
        $this->line("   ✓ recurring trans.  : {$recurring} baris dihapus.");

        // 5. cicilan
        $installments = DB::table('installments')->delete();
        $this->line("   ✓ installments      : {$installments} baris dihapus.");

        // 6. asset_prices (anak dari asset_holdings)
        $prices = DB::table('asset_prices')->delete();
        $this->line("   ✓ asset_prices      : {$prices} baris dihapus.");

        // 7. asset_holdings
        $holdings = DB::table('asset_holdings')->delete();
        $this->line("   ✓ asset_holdings    : {$holdings} baris dihapus.");

        $this->newLine();
        $this->info('✅ Database berhasil direset ke kondisi awal yang bersih.');
        $this->info('   Saldo semua akun kini 0 (dihitung dari journal entries yang sudah dihapus).');
        $this->info('   Silakan input saldo Modal Awal melalui: Transaksi → Jenis: Saldo Awal / Modal Awal.');
        $this->newLine();

        return self::SUCCESS;
    }
}
