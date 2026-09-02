<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Outlet;
use App\Services\Accounting\AssetDepreciationService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    protected AssetDepreciationService $deprecService;

    public function __construct(AssetDepreciationService $deprecService)
    {
        $this->deprecService = $deprecService;
    }

    public function index()
    {
        $assets = Asset::with(['outlet', 'depreciations'])->latest()->paginate(20);
        $outlets = Outlet::all();

        $totalPurchaseValue = (float) Asset::where('status', 'active')->sum('purchase_value');
        $totalBookValue = $assets->where('status', 'active')->sum(fn($a) => $a->current_book_value);
        $totalDepreciation = $totalPurchaseValue - $totalBookValue;

        return view('backend.accounts.assets.index', compact(
            'assets',
            'outlets',
            'totalPurchaseValue',
            'totalBookValue',
            'totalDepreciation'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string|max:100',
            'purchase_value'      => 'required|numeric|min:0.01',
            'purchase_date'       => 'required|date',
            'useful_life_years'   => 'required|integer|min:1|max:50',
            'depreciation_method' => 'required|in:straight_line,reducing_balance',
            'outlet_id'           => 'nullable|exists:outlets,id',
        ]);

        $count = Asset::count() + 1;
        $assetCode = 'FA-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        Asset::create([
            'company_id'          => 1,
            'asset_code'          => $assetCode,
            'name'                => $validated['name'],
            'category'            => $validated['category'],
            'purchase_value'      => $validated['purchase_value'],
            'purchase_date'       => $validated['purchase_date'],
            'useful_life_years'   => $validated['useful_life_years'],
            'depreciation_method' => $validated['depreciation_method'],
            'outlet_id'           => $validated['outlet_id'] ?? null,
            'status'              => 'active',
        ]);

        toastr()->success("Fixed Asset {$assetCode} registered successfully!");
        return redirect()->route('admin.assets.index');
    }

    public function runDepreciation(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        try {
            $res = $this->deprecService->runMonthlyDepreciation($validated['period']);
            toastr()->success("Monthly depreciation for period {$res['period']} posted successfully! ({$res['processed_count']} assets, Total: kr. " . number_format($res['total_depreciation'], 2) . ")");
        } catch (\Exception $e) {
            toastr()->error("Failed to run depreciation: " . $e->getMessage());
        }

        return redirect()->route('admin.assets.index');
    }
}
