<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankSyncController extends Controller
{
    public function index()
    {
        $workspaceId = session('active_workspace_id');
        $accounts = Account::where('category', 'asset')
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();

        return view('bank_sync.index', compact('accounts'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'bank_file' => 'required|file|mimes:csv,txt',
            'bank_type' => 'required|in:bca,mandiri,other',
        ]);

        $file = $request->file('bank_file');
        $data = [];
        
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            // Skip header if needed (BCA usually has some header lines)
            $row = 0;
            while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Simplified parsing logic
                // For BCA: usually Date, Description, Amount, Type (CR/DB)
                if (count($line) < 3) continue;
                
                $data[] = [
                    'date' => $line[0],
                    'description' => $line[1],
                    'amount' => str_replace(',', '', $line[2]),
                    'type' => $line[3] ?? 'DB',
                    'raw' => $line
                ];
                $row++;
            }
            fclose($handle);
        }

        $targetAccount = Account::find($request->account_id);
        $expenseAccounts = Account::where('type', 'expense')->where('is_postable', true)->get();

        return view('bank_sync.preview', compact('data', 'targetAccount', 'expenseAccounts'));
    }

    public function store(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        $transactions = $request->input('transactions', []);

        try {
            DB::transaction(function () use ($transactions, $workspaceId, $request) {
                foreach ($transactions as $index => $trx) {
                    if (!isset($trx['import'])) continue;

                    $journal = JournalEntry::create([
                        'workspace_id' => $workspaceId,
                        'date' => $trx['date'] ?? date('Y-m-d'),
                        'reference' => 'SYNC-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                        'description' => '[BANK-SYNC] ' . $trx['description'],
                    ]);

                    $amount = (float) $trx['amount'];
                    $isIncome = $trx['type'] === 'CR'; // Assumption CR = Credit = Income for Bank Side

                    if ($isIncome) {
                        // Debit Asset (Bank), Credit Income
                        $journal->lines()->create([
                            'account_id' => $request->target_account_id,
                            'debit' => $amount,
                            'credit' => 0,
                            'description' => $trx['description']
                        ]);
                        $journal->lines()->create([
                            'account_id' => $trx['to_account_id'], // Income account
                            'debit' => 0,
                            'credit' => $amount,
                            'description' => $trx['description']
                        ]);
                    } else {
                        // Debit Expense, Credit Asset (Bank)
                        $journal->lines()->create([
                            'account_id' => $trx['to_account_id'], // Expense account
                            'debit' => $amount,
                            'credit' => 0,
                            'description' => $trx['description']
                        ]);
                        $journal->lines()->create([
                            'account_id' => $request->target_account_id,
                            'debit' => 0,
                            'credit' => $amount,
                            'description' => $trx['description']
                        ]);
                    }
                }
            });

            return redirect()->route('journals.index')->with('success', 'Mutasi bank berhasil diimpor!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        }
    }
}
