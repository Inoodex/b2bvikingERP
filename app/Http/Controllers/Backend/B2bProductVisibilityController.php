<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\B2bVisibilityDataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\CustomerProductVisibility;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class B2bProductVisibilityController extends Controller
{
    /**
     * Display the B2B Stock Visibility Central Hub (Tier 1).
     */
    public function index(B2bVisibilityDataTable $dataTable)
    {
        // Calculate high-level KPIs for header metrics ribbon
        $stats = [
            'total_rules' => CustomerProductVisibility::count(),
            'priority_in_stock' => CustomerProductVisibility::where('visibility_mode', 'force_in_stock')->count(),
            'force_out_of_stock' => CustomerProductVisibility::where('visibility_mode', 'force_out_of_stock')->count(),
            'hide_product' => CustomerProductVisibility::where('visibility_mode', 'hide_product')->count(),
            'companies_count' => CustomerProductVisibility::whereNotNull('company_id')->distinct('company_id')->count('company_id'),
            'outlets_count' => CustomerProductVisibility::whereNotNull('outlet_id')->distinct('outlet_id')->count('outlet_id'),
        ];

        $companies = Company::orderBy('name')->get(['id', 'name']);
        
        // Exact 23 retail branch outlets (excluding central warehouse)
        $outlets = Outlet::where('type', '!=', 'warehouse')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        
        // Only registered retail/wholesale buyers
        $registeredBuyers = User::customers()
            ->where('status', 1)
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'phone']);

        return $dataTable->render('backend.b2b_stock_rules.index', compact(
            'stats',
            'companies',
            'outlets',
            'categories',
            'registeredBuyers'
        ));
    }

    /**
     * Store a new B2B customer stock visibility rule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'visibility_mode' => 'required|in:force_in_stock,force_out_of_stock,hide_product',
            'target_scope' => 'required|in:company,outlet,phone,user,buyer',
            'company_id' => 'nullable|required_if:target_scope,company|exists:companies,id',
            'outlet_id' => 'nullable|required_if:target_scope,outlet|exists:outlets,id',
            'user_id' => 'nullable|exists:users,id',
            'phone_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $companyId = $request->target_scope === 'company' ? $request->company_id : null;
        $outletId = $request->target_scope === 'outlet' ? $request->outlet_id : null;
        $userId = in_array($request->target_scope, ['phone', 'user', 'buyer']) ? $request->user_id : null;
        $phone = in_array($request->target_scope, ['phone', 'user', 'buyer']) ? $request->phone_number : null;

        if (!$companyId && !$outletId && !$userId && !$phone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please select at least one target Company, Outlet, or Buyer Phone.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $rule = CustomerProductVisibility::updateOrCreate(
                [
                    'product_id' => $request->product_id,
                    'company_id' => $companyId,
                    'outlet_id' => $outletId,
                    'user_id' => $userId,
                    'phone_number' => $phone,
                ],
                [
                    'visibility_mode' => $request->visibility_mode,
                    'notes' => $request->notes,
                    'created_by' => Auth::id() ?? 1,
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'B2B stock visibility rule saved successfully.',
                'rule' => $rule,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save rule: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing B2B stock rule.
     */
    public function update(Request $request, string $id)
    {
        $rule = CustomerProductVisibility::findOrFail($id);

        $request->validate([
            'visibility_mode' => 'required|in:force_in_stock,force_out_of_stock,hide_product',
            'notes' => 'nullable|string|max:255',
        ]);

        $rule->update([
            'visibility_mode' => $request->visibility_mode,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'B2B stock visibility rule updated successfully.',
            'rule' => $rule,
        ]);
    }

    /**
     * Delete an existing B2B stock rule.
     */
    public function destroy(string $id)
    {
        $rule = CustomerProductVisibility::findOrFail($id);
        $rule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'B2B customer stock rule removed successfully. Entity will revert to actual warehouse stock.',
        ]);
    }

    /**
     * Remote Select2 AJAX search for products (Zero-Lag Debounced Lookup).
     */
    public function searchProducts(Request $request)
    {
        $search = trim($request->get('q', ''));
        
        $products = Product::query()
            ->select(['id', 'name', 'product_number', 'sku', 'barcode', 'thumb_image', 'category_id'])
            ->with(['category:id,name'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('product_number', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->where('status', 1)
            ->limit(25)
            ->get();

        $formatted = $products->map(function ($product) {
            $thumb = $product->thumb_image ? asset($product->thumb_image) : asset('uploads/no-image.svg');
            $sku = $product->product_number ?? $product->sku ?? 'N/A';
            return [
                'id' => $product->id,
                'text' => $product->name . ' [' . $sku . ']',
                'name' => $product->name,
                'sku' => $sku,
                'thumb' => $thumb,
                'category' => optional($product->category)->name ?? 'General',
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Get Interactive Matrix View data (Mode A).
     */
    public function matrixData(Request $request)
    {
        $targetScope = $request->get('target_scope', 'company');
        $companyId = $targetScope === 'company' ? $request->get('company_id') : null;
        $outletId = $targetScope === 'outlet' ? $request->get('outlet_id') : null;
        $userId = in_array($targetScope, ['buyer', 'phone', 'user']) ? $request->get('user_id') : null;
        $phone = in_array($targetScope, ['buyer', 'phone', 'user']) ? $request->get('phone_number') : null;
        $categoryId = $request->get('category_id');
        $search = $request->get('search');

        // Fetch products matching filter
        $productsQuery = Product::query()
            ->with(['category', 'unit'])
            ->where('status', 1);

        if (!empty($categoryId) && is_numeric($categoryId) && intval($categoryId) > 0) {
            $productsQuery->where('category_id', intval($categoryId));
        }

        if (!empty($search)) {
            $search = trim($search);
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_number', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $productsQuery->orderBy('name')->paginate(20)->withQueryString();

        // Fetch existing overrides for this target entity ONLY if a target is selected
        $hasTarget = !empty($companyId) || !empty($outletId) || !empty($userId) || !empty($phone);
        $overrides = $hasTarget
            ? CustomerProductVisibility::query()
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when($phone, fn($q) => $q->where('phone_number', $phone))
                ->get()
                ->keyBy('product_id')
            : collect();

        return view('backend.b2b_stock_rules.partials.matrix_rows', compact(
            'products',
            'overrides',
            'targetScope',
            'companyId',
            'outletId',
            'userId',
            'phone',
            'hasTarget'
        ))->render();
    }

    /**
     * 1-Click Status Toggle inside the Matrix Grid.
     */
    public function matrixToggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_scope' => 'required|in:company,outlet,buyer,phone',
            'mode' => 'required|in:force_in_stock,force_out_of_stock,hide_product,standard',
            'company_id' => 'nullable|exists:companies,id',
            'outlet_id' => 'nullable|exists:outlets,id',
            'user_id' => 'nullable|exists:users,id',
            'phone_number' => 'nullable|string',
        ]);

        $companyId = $request->target_scope === 'company' ? $request->company_id : null;
        $outletId = $request->target_scope === 'outlet' ? $request->outlet_id : null;
        $userId = $request->target_scope === 'buyer' ? $request->user_id : null;
        $phone = $request->target_scope === 'phone' ? $request->phone_number : null;

        if (!$companyId && !$outletId && !$userId && !$phone) {
            return response()->json(['status' => 'error', 'message' => 'Target entity missing.'], 422);
        }

        if ($request->mode === 'standard') {
            // Remove override -> reverts to real warehouse stock
            CustomerProductVisibility::query()
                ->where('product_id', $request->product_id)
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when($phone, fn($q) => $q->where('phone_number', $phone))
                ->delete();

            return response()->json([
                'status' => 'success',
                'mode' => 'standard',
                'message' => 'Override removed. Product reverted to standard warehouse stock.',
            ]);
        }

        // Upsert rule
        $rule = CustomerProductVisibility::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'company_id' => $companyId,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'phone_number' => $phone,
            ],
            [
                'visibility_mode' => $request->mode,
                'created_by' => Auth::id() ?? 1,
            ]
        );

        return response()->json([
            'status' => 'success',
            'mode' => $request->mode,
            'message' => 'Availability rule updated to ' . str_replace('_', ' ', $request->mode),
            'rule' => $rule,
        ]);
    }

    /**
     * Bulk store or revert B2B stock visibility rules for multiple products at once (Tier 2).
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'visibility_mode' => 'required|in:force_in_stock,force_out_of_stock,hide_product,standard',
            'target_scope' => 'required|in:company,outlet,phone,user,buyer',
            'company_id' => 'nullable|required_if:target_scope,company|exists:companies,id',
            'outlet_id' => 'nullable|required_if:target_scope,outlet|exists:outlets,id',
            'user_id' => 'nullable|exists:users,id',
            'phone_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $companyId = $request->target_scope === 'company' ? $request->company_id : null;
        $outletId = $request->target_scope === 'outlet' ? $request->outlet_id : null;
        $userId = in_array($request->target_scope, ['phone', 'user', 'buyer']) ? $request->user_id : null;
        $phone = in_array($request->target_scope, ['phone', 'user', 'buyer']) ? $request->phone_number : null;

        if (!$companyId && !$outletId && !$userId && !$phone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please select at least one target Company, Outlet, or Buyer Phone.',
            ], 422);
        }

        $productIds = array_values(array_unique(array_filter($request->product_ids)));
        $count = count($productIds);

        DB::beginTransaction();
        try {
            if ($request->visibility_mode === 'standard') {
                // Remove all custom overrides for selected products and target entity -> reverts to warehouse real stock
                CustomerProductVisibility::query()
                    ->whereIn('product_id', $productIds)
                    ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                    ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->when($phone, fn($q) => $q->where('phone_number', $phone))
                    ->delete();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'count' => $count,
                    'message' => "Reverted {$count} products to standard warehouse stock successfully.",
                ]);
            }

            // Upsert records for each product
            foreach ($productIds as $productId) {
                CustomerProductVisibility::updateOrCreate(
                    [
                        'product_id' => $productId,
                        'company_id' => $companyId,
                        'outlet_id' => $outletId,
                        'user_id' => $userId,
                        'phone_number' => $phone,
                    ],
                    [
                        'visibility_mode' => $request->visibility_mode,
                        'notes' => $request->notes,
                        'created_by' => Auth::id() ?? 1,
                    ]
                );
            }

            DB::commit();

            $modeName = match($request->visibility_mode) {
                'force_in_stock' => 'Priority In-Stock',
                'force_out_of_stock' => 'Restricted (Out of Stock)',
                'hide_product' => 'Hidden from Catalog',
                default => $request->visibility_mode
            };

            return response()->json([
                'status' => 'success',
                'count' => $count,
                'message' => "Applied [{$modeName}] policy across {$count} products successfully.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to apply bulk rules: ' . $e->getMessage(),
            ], 500);
        }
    }
}
