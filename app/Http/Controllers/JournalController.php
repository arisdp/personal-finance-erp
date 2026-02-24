<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JournalEntry as Journal;
use App\Models\JournalLine;
use App\Models\Account;

class JournalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $journals = Journal::with('user')
            ->latest()
            ->paginate(15);


        $journals = Journal::latest()->get();
        $accounts = Account::orderBy('code')->get();

        return view('journals.index', compact('journals', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = Account::orderBy('code')->get();

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
        ]);

        DB::transaction(function () use ($request) {

            $totalDebit = collect($request->lines)->sum('debit');
            $totalCredit = collect($request->lines)->sum('credit');

            if ($totalDebit != $totalCredit) {
                throw new \Exception('Journal not balanced');
            }

            $journal = Journal::create([
                'date' => $request->date,
                'description' => $request->description,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->lines as $line) {

                if (($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0) {

                    $journal->lines()->create([
                        'account_id' => $line['account_id'],
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Journal saved successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
