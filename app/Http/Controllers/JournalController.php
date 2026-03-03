<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JournalEntry as Journal;
use App\Models\Account;
use Illuminate\Support\Str;

class JournalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workspaceId = session('active_workspace_id');

        $journals = Journal::with(['creator', 'lines.account'])
            ->where('workspace_id', $workspaceId)
            ->latest()
            ->paginate(15);

        return view('journals.index', compact('journals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Account is global master data, no workspace_id required
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();

        return view('journals.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string'
        ]);

        $workspaceId = session('active_workspace_id');

        if (!$workspaceId) {
            return response()->json([
                'success' => false,
                'message' => 'Active Workspace required to post a journal.'
            ], 403);
        }

        try {
            DB::transaction(function () use ($request, $workspaceId) {
                $totalDebit = collect($request->lines)->sum('debit');
                $totalCredit = collect($request->lines)->sum('credit');

                if (abs($totalDebit - $totalCredit) > 0.01) {
                    throw new \Exception('Journal not balanced. Debit: ' . $totalDebit . ', Credit: ' . $totalCredit);
                }

                $journal = Journal::create([
                    'workspace_id' => $workspaceId,
                    'date' => $request->date,
                    'reference' => 'JRN-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'description' => $request->description,
                ]);

                foreach ($request->lines as $line) {
                    if (($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0) {
                        $journal->lines()->create([
                            'account_id' => $line['account_id'],
                            'debit' => $line['debit'] ?? 0,
                            'credit' => $line['credit'] ?? 0,
                            'description' => $line['description'] ?? null
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Journal saved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workspaceId = session('active_workspace_id');
        $journal = Journal::with(['lines.account', 'creator', 'updater'])
            ->where('workspace_id', $workspaceId)
            ->findOrFail($id);
            
        return view('journals.show', compact('journal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $workspaceId = session('active_workspace_id');
        $journal = Journal::with('lines')
            ->where('workspace_id', $workspaceId)
            ->findOrFail($id);
            
        $accounts = Account::where('is_postable', true)->orderBy('code')->get();
        
        return view('journals.edit', compact('journal', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string'
        ]);

        $workspaceId = session('active_workspace_id');
        $journal = Journal::where('workspace_id', $workspaceId)->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $journal) {
                $totalDebit = collect($request->lines)->sum('debit');
                $totalCredit = collect($request->lines)->sum('credit');

                if (abs($totalDebit - $totalCredit) > 0.01) {
                    throw new \Exception('Journal not balanced. Debit: ' . $totalDebit . ', Credit: ' . $totalCredit);
                }

                $journal->update([
                    'date' => $request->date,
                    'description' => $request->description,
                ]);

                // Clear old lines and recreate (simplified edit for complex double entry)
                $journal->lines()->delete();

                foreach ($request->lines as $line) {
                    if (($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0) {
                        $journal->lines()->create([
                            'account_id' => $line['account_id'],
                            'debit' => $line['debit'] ?? 0,
                            'credit' => $line['credit'] ?? 0,
                            'description' => $line['description'] ?? null
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Journal updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workspaceId = session('active_workspace_id');
        $journal = Journal::where('workspace_id', $workspaceId)->findOrFail($id);
        
        $journal->delete(); // Soft delete

        return redirect()->route('journals.index')
            ->with('success', 'Transaksi berhasil dihapus (Soft Delete).');
    }
}
