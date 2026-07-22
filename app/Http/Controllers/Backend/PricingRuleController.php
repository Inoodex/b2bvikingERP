<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{
    public function index()
    {
        $pricingRules = PricingRule::orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('backend.pricing-rules.index', compact('pricingRules'));
    }

    public function create()
    {
        return view('backend.pricing-rules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255|unique:pricing_rules,name',
            'sale_multiplier' => 'required|numeric|min:0',
            'outlet_multiplier' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        $isDefault = (bool)($data['is_default'] ?? false);
        if ($isDefault) {
            PricingRule::query()->update(['is_default' => false]);
        }

        PricingRule::create([
            'name' => $data['name'],
            'sale_multiplier' => $data['sale_multiplier'],
            'outlet_multiplier' => $data['outlet_multiplier'],
            'is_default' => $isDefault,
            'status' => $data['status'],
        ]);

        Toastr::success('Pricing Rule Created Successfully!');
        return redirect()->route('admin.pricing-rules.index');
    }

    public function edit(string $id)
    {
        $pricingRule = PricingRule::findOrFail($id);
        return view('backend.pricing-rules.edit', compact('pricingRule'));
    }

    public function update(Request $request, string $id)
    {
        $pricingRule = PricingRule::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|max:255|unique:pricing_rules,name,' . $pricingRule->id,
            'sale_multiplier' => 'required|numeric|min:0',
            'outlet_multiplier' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'required|boolean',
        ]);

        $isDefault = (bool)($data['is_default'] ?? false);
        if ($isDefault) {
            PricingRule::query()->where('id', '!=', $pricingRule->id)->update(['is_default' => false]);
        }

        $pricingRule->update([
            'name' => $data['name'],
            'sale_multiplier' => $data['sale_multiplier'],
            'outlet_multiplier' => $data['outlet_multiplier'],
            'is_default' => $isDefault,
            'status' => $data['status'],
        ]);

        Toastr::success('Pricing Rule Updated Successfully!');
        return redirect()->route('admin.pricing-rules.index');
    }

    public function destroy(string $id)
    {
        $pricingRule = PricingRule::findOrFail($id);
        $pricingRule->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
}

