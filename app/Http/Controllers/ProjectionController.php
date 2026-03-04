<?php

namespace App\Http\Controllers;

use App\Services\ProjectionService;
use App\Services\FinancialSummaryService;
use Illuminate\Http\Request;

class ProjectionController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = session('active_workspace_id');
        if (!$workspaceId) {
            return redirect()->route('workspaces.index')->with('error', 'Silakan pilih workspace terlebih dahulu.');
        }

        $annualReturn = $request->get('annual_return', 7);
        $annualInflation = $request->get('annual_inflation', 3);
        $years = $request->get('years', 10);

        $service = new ProjectionService($workspaceId);
        $projections = $service->calculate($annualReturn, $annualInflation, $years);

        // Get monthly savings average for reference
        $summary = new FinancialSummaryService($workspaceId);
        $currentNetWorth = $summary->getNetWorth();

        return view('reports.projections', compact(
            'projections', 
            'annualReturn', 
            'annualInflation', 
            'years',
            'currentNetWorth'
        ));
    }
}
