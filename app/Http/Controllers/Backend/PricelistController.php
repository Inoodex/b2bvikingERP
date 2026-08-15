<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PricelistDataTable;
use App\Http\Controllers\Controller;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\Product;
use App\Services\Pricing\PricelistResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PricelistController extends Controller
{
    protected PricelistResolverService $resolver;

    public function __construct(PricelistResolverService $resolver)
    {
        $this->resolver = $resolver;
    }

    public function index(PricelistDataTable $dataTable)
    {
        return $dataTable->render('backend.pricelist.index');
    }

    public function create(): View
    {
        $products = Product::where('status', 1)->with('variants')->get();
        return view('backend.pricelist.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'customer_segment' => 'required|in:retail,wholesale,b2b_vip,distributor',
            'region' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $pricelist = Pricelist::create([
                'name' => $request->name,
                'customer_segment' => $request->customer_segment,
                'region' => $request->region,
                'valid_from' => $request->valid_from,
                'valid_to' => $request->valid_to,
                'status' => $request->status,
            ]);

            foreach ($request->items as $item) {
                PricelistItem::create([
                    'pricelist_id' => $pricelist->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            toastr()->success('Pricelist "' . $pricelist->name . '" created successfully!');
            return redirect()->route('admin.pricelists.index');
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to create Pricelist: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(Pricelist $pricelist): View
    {
        $pricelist->load(['items.product', 'items.variant']);
        $products = Product::where('status', 1)->with('variants')->get();
        return view('backend.pricelist.edit', compact('pricelist', 'products'));
    }

    public function update(Request $request, Pricelist $pricelist): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'customer_segment' => 'required|in:retail,wholesale,b2b_vip,distributor',
            'region' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $pricelist->update([
                'name' => $request->name,
                'customer_segment' => $request->customer_segment,
                'region' => $request->region,
                'valid_from' => $request->valid_from,
                'valid_to' => $request->valid_to,
                'status' => $request->status,
            ]);

            $pricelist->items()->delete();
            foreach ($request->items as $item) {
                PricelistItem::create([
                    'pricelist_id' => $pricelist->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            toastr()->success('Pricelist "' . $pricelist->name . '" updated successfully!');
            return redirect()->route('admin.pricelists.index');
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Failed to update Pricelist: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $pricelist = Pricelist::findOrFail($id);
            $pricelist->items()->delete();
            $pricelist->delete();

            return response(['status' => 'success', 'message' => 'Pricelist deleted successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete Pricelist: ' . $e->getMessage()], 500);
        }
    }

    public function changeStatus(Request $request): JsonResponse
    {
        $pricelist = Pricelist::findOrFail($request->id);
        $pricelist->status = $request->status == 'true' ? 1 : 0;
        $pricelist->save();

        return response()->json(['status' => 'success', 'message' => 'Pricelist status updated successfully!']);
    }

    public function resolvePrice(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'customer_id' => 'nullable|integer',
            'variant_id' => 'nullable|integer',
        ]);

        $result = $this->resolver->resolvePrice(
            $request->customer_id ? (int)$request->customer_id : null,
            (int)$request->product_id,
            $request->variant_id ? (int)$request->variant_id : null
        );

        return response()->json($result);
    }
}
