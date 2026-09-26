<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HandlingUnit;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Barcode\Gs1BarcodeService;
use App\Services\Barcode\HandlingUnitService;
use App\Services\Barcode\NativeBarcodeGenerator;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HandlingUnitController extends Controller
{
    public function __construct(
        protected HandlingUnitService $huService,
        protected Gs1BarcodeService $gs1BarcodeService,
        protected NativeBarcodeGenerator $barcodeGenerator
    ) {}

    /**
     * Display a listing of Packaging & Handling Units.
     */
    public function index(Request $request): View
    {
        $query = (string) $request->input('q', '');
        $packagingTypeId = $request->filled('packaging_type_id') ? (int) $request->input('packaging_type_id') : null;
        $status = $request->filled('status') ? (string) $request->input('status') : null;

        $packagingTypes = PackagingType::active()->get();

        $units = $this->huService->searchUnits(
            query: $query,
            packagingTypeId: $packagingTypeId,
            status: $status,
            limit: 50
        );

        $stats = [
            'total_units' => HandlingUnit::count(),
            'total_items_packed' => HandlingUnit::whereNull('parent_handling_unit_id')->sum('total_quantity'),
            'total_cartons' => HandlingUnit::whereHas('packagingType', fn($q) => $q->where('code', 'CTN'))->count(),
            'total_pallets' => HandlingUnit::whereHas('packagingType', fn($q) => $q->where('code', 'PLT'))->count(),
        ];

        return view('backend.packaging_units.index', compact('units', 'packagingTypes', 'stats', 'query', 'packagingTypeId', 'status'));
    }

    /**
     * Show the form for creating a new Handling Unit (Packing Studio).
     */
    public function create(): View
    {
        $packagingTypes = PackagingType::active()->get();
        $categories = Category::select('id', 'name', 'gpc_code', 'gpc_title')->where('status', 1)->orderBy('name')->get();
        $taxonomyResolver = app(\App\Services\Barcode\Gs1TaxonomyResolver::class);
        $categories->each(function ($cat) use ($taxonomyResolver) {
            if (empty($cat->gpc_code)) {
                $resolved = $taxonomyResolver->resolve($cat->name);
                $cat->gpc_code = $resolved['code'];
                $cat->gpc_title = $resolved['title'];
            }
        });
        $potentialParents = HandlingUnit::with('packagingType')
            ->whereIn('status', ['packed', 'sealed'])
            ->latest('id')
            ->limit(30)
            ->get();

        $availableChildUnits = HandlingUnit::with(['packagingType', 'items.product'])
            ->whereNull('parent_handling_unit_id')
            ->whereIn('status', ['packed', 'sealed', 'draft'])
            ->latest('id')
            ->limit(50)
            ->get();

        $totalProductsCount = Product::count();
        $initialProducts = app(\App\Services\Barcode\BarcodeLabelService::class)->searchProducts('', null, 60);

        return view('backend.packaging_units.create', compact(
            'packagingTypes',
            'categories',
            'potentialParents',
            'availableChildUnits',
            'initialProducts',
            'totalProductsCount'
        ));
    }

    /**
     * Store a newly created Handling Unit in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'packaging_type_id' => ['required', 'exists:packaging_types,id'],
            'parent_handling_unit_id' => ['nullable', 'exists:handling_units,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'hu_code' => ['nullable', 'string', 'max:100', 'unique:handling_units,hu_code'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'gross_weight' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:draft,packed,sealed,shipped,received'],
            'items' => ['nullable'],
            'child_unit_ids' => ['nullable', 'array'],
        ]);

        if (is_string($validated['items'] ?? null)) {
            $validated['items'] = json_decode($validated['items'], true);
        }

        // Strict backend stock availability validation
        $items = $validated['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($productId <= 0) {
                    continue;
                }

                $product = Product::find($productId);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => [__("Product with ID #:id not found.", ['id' => $productId])],
                    ]);
                }

                $variant = $variantId ? ProductVariant::find($variantId) : null;
                $availableStock = (int) ($variant ? ($variant->total_stock ?? $variant->qty ?? 0) : ($product->total_stock ?? $product->qty ?? 0));
                $itemName = $product->name . ($variant ? " ({$variant->name})" : '');

                if ($availableStock <= 0) {
                    throw ValidationException::withMessages([
                        'items' => [__("Cannot pack out-of-stock item ':name'. Current available stock is 0.", ['name' => $itemName])],
                    ]);
                }

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => [__("Packing quantity for ':name' must be at least 1.", ['name' => $itemName])],
                    ]);
                }

                if ($quantity > $availableStock) {
                    throw ValidationException::withMessages([
                        'items' => [__("Packing quantity (:qty) for ':name' exceeds available warehouse stock (:stock).", [
                            'qty' => $quantity,
                            'name' => $itemName,
                            'stock' => $availableStock,
                        ])],
                    ]);
                }
            }
        }

        $unit = $this->huService->createHandlingUnit($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Handling Unit created successfully!'),
                'unit' => $unit,
                'redirect' => route('admin.packaging-units.show', $unit->id),
            ]);
        }

        Toastr::success(__('Handling Unit created successfully!'));

        return redirect()->route('admin.packaging-units.show', $unit->id);
    }

    /**
     * Display details and contents breakdown of a Handling Unit.
     */
    public function show(HandlingUnit $handlingUnit): View
    {
        $handlingUnit->load([
            'packagingType',
            'parentUnit.packagingType',
            'childUnits.packagingType',
            'childUnits.items.product',
            'childUnits.items.variant',
            'items.product.category',
            'items.variant',
            'packer',
        ]);

        $barcodeSvg = $this->barcodeGenerator->getBarcodeSvg($handlingUnit->hu_code, 60, 1.8, 8);
        $flattenedContents = $handlingUnit->getFlattenedContents();

        return view('backend.packaging_units.show', compact('handlingUnit', 'barcodeSvg', 'flattenedContents'));
    }

    /**
     * Render high-resolution 4×6 inch (100×150mm) GS1 Logistics shipping label for print.
     */
    public function printLabel(HandlingUnit $handlingUnit): View
    {
        $handlingUnit->load([
            'packagingType',
            'parentUnit.packagingType',
            'items.product.category',
            'items.variant',
            'packer',
        ]);

        $barcodeSvg = $this->barcodeGenerator->getBarcodeSvg($handlingUnit->hu_code, 75, 2.0, 10);
        $flattenedContents = $handlingUnit->getFlattenedContents();

        return view('backend.packaging_units.print_label', compact('handlingUnit', 'barcodeSvg', 'flattenedContents'));
    }

    /**
     * Nest another Handling Unit inside this Handling Unit.
     */
    public function nestUnit(Request $request, HandlingUnit $handlingUnit): JsonResponse
    {
        $request->validate([
            'child_unit_id' => ['required', 'exists:handling_units,id'],
        ]);

        $child = HandlingUnit::findOrFail((int) $request->input('child_unit_id'));
        $this->huService->nestChildUnit($handlingUnit, $child);

        return response()->json([
            'success' => true,
            'message' => __("Unit {$child->hu_code} nested into {$handlingUnit->hu_code} successfully!"),
            'total_quantity' => $handlingUnit->fresh()->total_quantity,
        ]);
    }

    /**
     * Unpack container / mark as unpacked.
     */
    public function unpack(HandlingUnit $handlingUnit): RedirectResponse
    {
        $handlingUnit->update(['status' => 'unpacked']);
        Toastr::info(__('Handling Unit marked as unpacked.'));

        return redirect()->route('admin.packaging-units.show', $handlingUnit->id);
    }

    /**
     * Soft delete handling unit.
     */
    public function destroy(HandlingUnit $handlingUnit): JsonResponse
    {
        // Unlink any children first
        HandlingUnit::where('parent_handling_unit_id', $handlingUnit->id)->update(['parent_handling_unit_id' => null]);
        $handlingUnit->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Handling Unit deleted successfully!'),
        ]);
    }

    /**
     * WMS 1-Scan Bulk Inward Receiving by Container Barcode.
     */
    public function scanReceive(Request $request): JsonResponse
    {
        $request->validate([
            'hu_code' => ['required', 'string'],
            'warehouse_id' => ['nullable', 'integer'],
        ]);

        try {
            $unit = $this->huService->receiveUnitByBarcode(
                huCode: (string) $request->input('hu_code'),
                warehouseId: $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null
            );

            return response()->json([
                'success' => true,
                'message' => __("Handling Unit [{$unit->hu_code}] received successfully with {$unit->total_quantity} total items!"),
                'hu_code' => $unit->hu_code,
                'status' => $unit->status,
                'total_quantity' => $unit->total_quantity,
                'items_breakdown' => $unit->getFlattenedContents(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * WMS 1-Scan Bulk Dispatch by Container Barcode.
     */
    public function scanShip(Request $request): JsonResponse
    {
        $request->validate([
            'hu_code' => ['required', 'string'],
        ]);

        try {
            $unit = $this->huService->shipUnitByBarcode((string) $request->input('hu_code'));

            return response()->json([
                'success' => true,
                'message' => __("Handling Unit [{$unit->hu_code}] dispatched/shipped successfully!"),
                'hu_code' => $unit->hu_code,
                'status' => $unit->status,
                'total_quantity' => $unit->total_quantity,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
