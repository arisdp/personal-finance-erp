<?php

namespace App\Http\Controllers;

use App\Models\InvestmentInstrument;
use App\Models\AssetHolding;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvestmentInstrumentController extends Controller
{
    /**
     * Display list of instruments with aggregate data.
     */
    public function index()
    {
        $workspaceId = session('active_workspace_id');

        $instruments = InvestmentInstrument::with(['holdings' => function ($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        }])->orderBy('asset_type')->orderBy('ticker')->get();

        return view('investment_instruments.index', compact('instruments'));
    }

    /**
     * Store a new instrument.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ticker'        => 'required|string|max:20|unique:investment_instruments,ticker',
            'name'          => 'required|string|max:255',
            'asset_type'    => 'required|string',
            'current_price' => 'required|numeric|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        InvestmentInstrument::create([
            'ticker'            => strtoupper($request->ticker),
            'name'              => $request->name,
            'asset_type'        => $request->asset_type,
            'current_price'     => $request->current_price,
            'last_price_update' => Carbon::now(),
            'notes'             => $request->notes,
        ]);

        return redirect()->route('investment_instruments.index')
            ->with('success', "Instrumen {$request->ticker} berhasil ditambahkan.");
    }

    /**
     * Update the current price for an instrument.
     * This automatically affects all linked holdings' P/L calculation.
     */
    public function updatePrice(Request $request, InvestmentInstrument $investmentInstrument)
    {
        $request->validate([
            'new_price' => 'required|numeric|min:0',
        ]);

        $investmentInstrument->update([
            'current_price'     => $request->new_price,
            'last_price_update' => Carbon::now(),
        ]);

        // Also sync current_price on individual holdings (for backward compatibility / standalone use)
        AssetHolding::where('instrument_id', $investmentInstrument->id)
            ->update(['current_price' => $request->new_price, 'last_updated' => Carbon::now()]);

        $ticker = $investmentInstrument->ticker;
        return back()->with('success', "Harga {$ticker} berhasil diperbarui ke Rp " . number_format($request->new_price, 0, ',', '.'));
    }

    /**
     * Delete an instrument (holdings become unlinked, not deleted).
     */
    public function destroy(InvestmentInstrument $investmentInstrument)
    {
        // Unlink holdings before deleting
        AssetHolding::where('instrument_id', $investmentInstrument->id)
            ->update(['instrument_id' => null]);

        $investmentInstrument->delete();

        return redirect()->route('investment_instruments.index')
            ->with('success', 'Instrumen dihapus. Holdings yang terkait tidak ikut terhapus.');
    }
}
