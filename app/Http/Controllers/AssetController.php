<?php

namespace App\Http\Controllers;

use App\Models\AssetHolding;
use App\Models\AssetPrice;
use App\Models\Account;
use App\Models\InvestmentInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetController extends Controller
{
    /**
     * Display investment dashboard.
     */
    public function index()
    {
        $workspaceId = session('active_workspace_id');
        
        $holdings = AssetHolding::with('account')
            ->where('workspace_id', $workspaceId)
            ->get();

        $totalCost = $holdings->sum('cost_basis');
        $totalMarketValue = $holdings->sum('market_value');
        $totalGainLoss = $totalMarketValue - $totalCost;
        $gainLossPercentage = $totalCost > 0 ? ($totalGainLoss / $totalCost) * 100 : 0;

        return view('investments.index', compact(
            'holdings', 
            'totalCost', 
            'totalMarketValue', 
            'totalGainLoss', 
            'gainLossPercentage'
        ));
    }

    /**
     * Show form to create new asset holding.
     */
    public function create()
    {
        $accounts = Account::where('category', 'asset')
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();

        $instruments = InvestmentInstrument::orderBy('ticker')->get();

        return view('investments.create', compact('accounts', 'instruments'));
    }

    /**
     * Store a newly created asset holding.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'asset_name' => 'required|string|max:255',
            'asset_type' => 'required|string',
            'ticker' => 'nullable|string|max:20',
            'quantity' => 'required|numeric|min:0',
            'avg_buy_price' => 'required|numeric|min:0',
            'current_price' => 'required|numeric|min:0',
        ]);

        $workspaceId = session('active_workspace_id');

        // Link to master instrument if exists
        $instrumentId = null;
        if ($request->ticker) {
            $instrument = InvestmentInstrument::where('ticker', strtoupper($request->ticker))->first();
            if ($instrument) {
                $instrumentId = $instrument->id;
                // If linked, we might want to ensure the asset_name and asset_type match for consistency
                // but for now we'll just link it.
            }
        }

        AssetHolding::create([
            'workspace_id'  => $workspaceId,
            'instrument_id' => $instrumentId,
            'account_id'    => $request->account_id,
            'asset_name'    => $request->asset_name,
            'asset_type'    => $request->asset_type,
            'ticker'        => strtoupper($request->ticker),
            'quantity'      => $request->quantity,
            'avg_buy_price' => $request->avg_buy_price,
            'current_price' => $request->current_price,
            'last_updated'  => Carbon::now(),
        ]);

        // Log initial price if provided
        AssetPrice::create([
            'account_id' => $request->account_id,
            'asset_type' => $request->asset_type,
            'ticker' => $request->ticker,
            'price' => $request->current_price,
            'price_date' => Carbon::now(),
            'source' => 'Manual Input',
        ]);

        return redirect()->route('investments.index')
            ->with('success', 'Asset berhasil ditambahkan ke portofolio.');
    }

    /**
     * Update asset price.
     */
    public function updatePrice(Request $request, AssetHolding $investment)
    {
        $request->validate([
            'new_price' => 'required|numeric|min:0',
        ]);

        $investment->update([
            'current_price' => $request->new_price,
            'last_updated' => Carbon::now(),
        ]);

        AssetPrice::create([
            'account_id' => $investment->account_id,
            'asset_type' => $investment->asset_type,
            'ticker' => $investment->ticker,
            'price' => $request->new_price,
            'price_date' => Carbon::now(),
            'source' => 'Price Update',
        ]);

        return back()->with('success', 'Harga pasar berhasil diperbarui.');
    }

    /**
     * Remove asset from portfolio.
     */
    public function destroy(AssetHolding $investment)
    {
        $investment->delete();
        return redirect()->route('investments.index')
            ->with('success', 'Asset dihapus dari portofolio.');
    }
}
