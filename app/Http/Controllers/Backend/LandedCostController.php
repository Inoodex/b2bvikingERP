<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\LandedCostService;
use Illuminate\Http\Request;

class LandedCostController extends Controller
{
    protected LandedCostService $landedCostService;

    public function __construct(LandedCostService $landedCostService)
    {
        $this->landedCostService = $landedCostService;
    }

    public function show($purchase_id)
    {
        $purchase = Purchase::with([
            'vendor',
            'currency',
            'letterOfCredit.expenses',
            'items.product',
            'items.variant'
        ])->findOrFail($purchase_id);

        $matrix = $this->landedCostService->calculateLandedCosts($purchase);

        return view('backend.landed_cost.show', compact('purchase', 'matrix'));
    }

    public function recalculate($purchase_id)
    {
        $purchase = Purchase::findOrFail($purchase_id);
        $this->landedCostService->calculateLandedCosts($purchase);

        return redirect()->back()->with('success', 'Landed costs recalculated successfully based on latest LC expenses and accepted quantities.');
    }
}
