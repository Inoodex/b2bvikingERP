<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductAnnouncementDataTable;
use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCreateRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Brand;
use App\Models\ProductType;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Unit;
use App\Models\Vendor;
use App\Traits\ImageUploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\InventoryStock;
use App\Models\StockLedger;
use App\Jobs\DispatchProductAnnouncementChunksJob;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Events\ProductsPublished;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    use ImageUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('role:Admin', except: ['index']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants.color', 'variants.size', 'inventoryStocks', 'vendor']);
        
        // Visibility Constraints for non-admins
        if (!Auth::user()->hasRole('Admin')) {
            $query->where('status', 1)
                  ->whereHas('category', function($q) {
                      $q->where('status', 1);
                  })
                  ->where(function($q) {
                      $q->whereNull('product_type_id')
                        ->orWhereHas('productType', function($sq) {
                            $sq->where('status', 1);
                        });
                  });
        }

        // Handle Sorting
        $sort = $request->sort ?? 'latest';
        if ($sort == 'z-a') {
            $query->orderBy('name', 'desc');
        } elseif ($sort == 'a-z') {
            $query->orderBy('name', 'asc');
        } elseif ($sort == 'active') {
            $query->where('status', 1)->latest();
        } elseif ($sort == 'inactive') {
            $query->where('status', 0)->latest();
        } else {
            $query->latest();
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_number', 'like', "%{$search}%")
                    ->orWhere('self_number', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('sub_category') && $request->sub_category != '') {
            $query->where('sub_category_id', $request->sub_category);
        }

        if ($request->has('child_category') && $request->child_category != '') {
            $query->where('child_category_id', $request->child_category);
        }

        if ($request->has('product_type') && $request->product_type != '') {
            $type = $request->product_type;
            if (is_numeric($type)) {
                $query->where('product_type_id', $type);
            } else {
                $query->where('product_type', $type);
            }
        }

        if ($request->has('alphabet') && $request->alphabet != '') {
            $query->where('name', 'like', $request->alphabet . '%');
        }

        if ($request->has('vendor') && $request->vendor != '') {
            $query->where('vendor_id', $request->vendor);
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::where('status', 1)->get();
        $productTypes = ProductType::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        
        if ($request->ajax()) {
            return view('backend.product.product_grid', compact('products'))->render();
        }

        return view('backend.product.index', compact('products', 'categories', 'productTypes', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        $colors = Color::where('status', 1)->get();
        $sizes = Size::where('status', 1)->get();
        $productTypes = ProductType::where('status', 1)->get();
        
        return view('backend.product.create', compact('categories', 'brands', 'units', 'vendors', 'colors', 'sizes', 'productTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $discountConfig = $this->normalizeDiscountInput($request);
            $vatConfig = $this->normalizeVatInput($request);
            $imagePath = $this->upload_image($request, 'image', 'uploads/products');
            $variantRows = $this->extractVariantRows($request->input('variants', []), false);
            $hasVariantRows = !empty($variantRows);

            $product = new Product();
            $product->thumb_image = $imagePath;
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->child_category_id = $request->child_category_id;
            $product->brand_id = $request->brand_id;
            $product->vendor_id = $request->vendor_id;
            $product->unit_id = $request->unit_id;
            $product->product_number = $request->product_number;
            $product->long_description = $request->long_description;
            $product->purchase_price = $request->purchase_price ?? 0;
            $product->price = $request->price ?? 0;
            $product->outlet_price = $request->outlet_price ?? 0;
            $product->barcode = $request->barcode;
            $product->status = $request->status;
            $product->product_type = $request->product_type;
            $product->product_type_id = $request->product_type_id;
            $product->custom_label = $request->custom_label;
            $product->self_number = $request->self_number;
            $product->raw_material_cost = max(0, (float) ($request->raw_material_cost ?? 0));
            $product->transport_cost = max(0, (float) ($request->transport_cost ?? 0));
            $product->tax = max(0, (float) ($request->tax ?? 0));
            $product->minimum_order_qty = max(1, (int) ($request->minimum_order_qty ?? 1));
            $product->discount_type = $discountConfig['type'];
            $product->discount = $discountConfig['value'];
            $product->vat_type = $vatConfig['type'];
            $product->vat_value = $vatConfig['value'];
            
            // Set qty for backward compatibility if needed, but we reflect in InventoryStock
            $product->qty = $hasVariantRows ? 0 : max(0, (int) ($request->qty ?? 0));
            $product->save();

            // Handle Product Opening Stock
            if ($product->qty > 0 && !$hasVariantRows) {
                $stock = InventoryStock::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1 // Default
                ]);
                $stock->increment('quantity', $product->qty);

                StockLedger::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1,
                    'reference_type' => 'opening',
                    'reference_id' => $product->id,
                    'in_qty' => $product->qty,
                    'out_qty' => 0,
                    'balance_qty' => $stock->quantity,
                    'date' => date('Y-m-d')
                ]);
            }

            // Handle Variants
            foreach ($variantRows as $row) {
                $productVariant = new ProductVariant();
                $productVariant->product_id = $product->id;
                $productVariant->color_id = $row['color_id'];
                $productVariant->size_id = $row['size_id'];
                $productVariant->qty = $row['qty'];
                $productVariant->name = $this->resolveVariantDisplayName($row['color_id'], $row['size_id']);
                $productVariant->price = $row['price'];
                $productVariant->outlet_price = $row['outlet_price'];
                $productVariant->save();

                // Variant Opening Stock
                if ($productVariant->qty > 0) {
                    $stock = InventoryStock::firstOrCreate([
                        'product_id' => $product->id,
                        'variant_id' => $productVariant->id,
                        'outlet_id' => 1 // Default
                    ]);
                    $stock->increment('quantity', $productVariant->qty);

                    StockLedger::create([
                        'product_id' => $product->id,
                        'variant_id' => $productVariant->id,
                        'outlet_id' => 1,
                        'reference_type' => 'opening',
                        'reference_id' => $productVariant->id,
                        'in_qty' => $productVariant->qty,
                        'out_qty' => 0,
                        'balance_qty' => $stock->quantity,
                        'date' => date('Y-m-d')
                    ]);
                }
            }

            DB::commit();
            Toastr::success('Product Created Successfully!');
            return redirect()->route('admin.products.index');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
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
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->get();
        $brands = Brand::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        $colors = Color::where('status', 1)->get();
        $sizes = Size::where('status', 1)->get();
        $productTypes = ProductType::where('status', 1)->get();
        
        return view('backend.product.edit', compact('product', 'categories', 'subCategories', 'childCategories', 'brands', 'units', 'vendors', 'colors', 'sizes', 'productTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $discountConfig = $this->normalizeDiscountInput($request);
            $vatConfig = $this->normalizeVatInput($request);
            $variantRows = $this->extractVariantRows($request->input('variants', []), true);
            $hasVariantRows = !empty($variantRows);
            $product = Product::findOrFail($id);
            $imagePath = $this->update_image($request, 'image', 'uploads/products', $product->thumb_image);

            if ($request->hasFile('image')) {
                $product->thumb_image = $imagePath;
            }

            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->child_category_id = $request->child_category_id;
            $product->brand_id = $request->brand_id;
            $product->vendor_id = $request->vendor_id;
            $product->unit_id = $request->unit_id;
            $product->product_number = $request->product_number;
            $product->long_description = $request->long_description;
            $product->purchase_price = $request->purchase_price ?? 0;
            $product->price = $request->price ?? 0;
            $product->outlet_price = $request->outlet_price ?? 0;
            $product->barcode = $request->barcode;
            $product->status = $request->status;
            $product->product_type = $request->product_type;
            $product->product_type_id = $request->product_type_id;
            $product->custom_label = $request->custom_label;
            $product->self_number = $request->self_number;
            $product->raw_material_cost = max(0, (float) ($request->raw_material_cost ?? 0));
            $product->transport_cost = max(0, (float) ($request->transport_cost ?? 0));
            $product->tax = max(0, (float) ($request->tax ?? 0));
            $product->minimum_order_qty = max(1, (int) ($request->minimum_order_qty ?? 1));
            $product->discount_type = $discountConfig['type'];
            $product->discount = $discountConfig['value'];
            $product->vat_type = $vatConfig['type'];
            $product->vat_value = $vatConfig['value'];
            
            if ($hasVariantRows) {
                $product->qty = 0;
            }
            $product->save();

            // Handle Product Manual Stock Adjustment
            $adjustment = 0;
            if (!$hasVariantRows && $request->has('current_stock')) {
                $currentDbStock = $product->inventory_stock;
                $submittedStock = (float)$request->current_stock;
                
                if ($submittedStock != $currentDbStock) {
                    $adjustment = $submittedStock - $currentDbStock;
                }
            }
            
            if ($adjustment != 0) {
                $stock = InventoryStock::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1
                ]);
                $stock->increment('quantity', $adjustment);

                StockLedger::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1,
                    'reference_type' => 'adjustment',
                    'reference_id' => $product->id,
                    'in_qty' => $adjustment > 0 ? $adjustment : 0,
                    'out_qty' => $adjustment < 0 ? abs($adjustment) : 0,
                    'balance_qty' => $stock->quantity,
                    'date' => date('Y-m-d')
                ]);
                
                $product->increment('qty', $adjustment);
            }

            // Non-destructive Variant Update
            $keepVariantIds = [];
            foreach ($variantRows as $vData) {
                $variant = null;
                if (isset($vData['id'])) {
                    $variant = ProductVariant::where('product_id', $product->id)->find($vData['id']);
                }

                if (!$variant) {
                    $variant = new ProductVariant();
                    $variant->product_id = $product->id;
                }

                $variant->color_id = $vData['color_id'];
                $variant->size_id = $vData['size_id'];
                $variant->name = $this->resolveVariantDisplayName($vData['color_id'], $vData['size_id']);
                $variant->price = $vData['price'];
                $variant->outlet_price = $vData['outlet_price'];
                $variant->save();
                
                $keepVariantIds[] = $variant->id;

                // Variant Manual Stock Adjustment
                $vAdjustment = 0;
                $vCurrentDbStock = $variant->inventory_stock ?? 0;
                $vSubmittedVal = (float) ($vData['current_stock'] ?? 0);
                if ($vSubmittedVal != $vCurrentDbStock) {
                    $vAdjustment = $vSubmittedVal - $vCurrentDbStock;
                }
                
                if ($vAdjustment != 0) {
                    $vStock = InventoryStock::firstOrCreate([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'outlet_id' => 1
                    ]);
                    $vStock->increment('quantity', $vAdjustment);

                    StockLedger::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'outlet_id' => 1,
                        'reference_type' => 'adjustment',
                        'reference_id' => $variant->id,
                        'in_qty' => $vAdjustment > 0 ? $vAdjustment : 0,
                        'out_qty' => $vAdjustment < 0 ? abs($vAdjustment) : 0,
                        'balance_qty' => $vStock->quantity,
                        'date' => date('Y-m-d')
                    ]);
                    
                    $variant->increment('qty', $vAdjustment);
                }
            }

            // Delete variants not in request
            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keepVariantIds)->delete();

            DB::commit();
            Toastr::success('Product Updated Successfully!');
            
            if ($request->has('return_url')) {
                return redirect($request->return_url);
            }
            
            return redirect()->route('admin.products.index');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        $this->delete_image($product->thumb_image);
        $product->delete();
        
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change product status.
     */
    public function changeStatus(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->status = $request->status == 'true' ? 1 : 0;
        $product->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }

    /**
     * Display the import form
     */
    public function importView()
    {
        return view('backend.product.import');
    }

    /**
     * Preview the uploaded file
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:csv,xlsx,xls|max:204800'
        ]);

        try {
            $file = $request->file('import_file');
            $originalName = $file->getClientOriginalName();
            
            $tempName = 'temp_import_' . time() . '_' . $originalName;
            $path = $file->storeAs('temp', $tempName, 'public');
            $fullPath = Storage::disk('public')->path($path);

            $importer = new ProductsImport();
            $preview = $importer->getPreviewData($fullPath, $originalName);

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'temp_path' => $path,
                'original_name' => $originalName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Handle the Excel import
     */
    public function importStore(Request $request)
    {
        if ($request->has('temp_path')) {
            $request->validate([
                'temp_path' => 'required',
                'original_name' => 'required'
            ]);
            $tempPath = $request->temp_path;
            $originalName = $request->original_name;
            $fullPath = Storage::disk('public')->path($tempPath);
        } else {
            $request->validate([
                'import_file' => 'required|mimes:csv,xlsx,xls|max:204800'
            ], [
                'import_file.required' => 'Please upload a file',
                'import_file.mimes' => 'Only CSV, xlsx, and xls files are allowed',
                'import_file.max' => 'File size must be less than 200MB'
            ]);
            $file = $request->file('import_file');
            $fullPath = $file->getRealPath();
            $originalName = $file->getClientOriginalName();
            $tempPath = null;
        }

        try {
            if (!file_exists($fullPath)) {
                throw new \Exception('Could not access file');
            }
            
            $importer = new ProductsImport();
            $results = $importer->import($fullPath, $originalName);

            $createdProductIds = collect($results['created_product_ids'] ?? [])
                ->map(static fn ($id): int => (int) $id)
                ->filter(static fn ($id): bool => $id > 0)
                ->unique()
                ->values()
                ->all();

            if ($tempPath) {
                Storage::disk('public')->delete($tempPath);
            }
            
            $message = 'Import completed! Success: ' . $results['success'] . ', Skipped: ' . ($results['skipped'] ?? 0) . ', Failed: ' . $results['failed'];
            
            if (!empty($results['errors'])) {
                $message .= ' Errors found in some rows.';
            }

            Toastr::success($message);
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('admin.products.index')]);
            }
            return redirect()->route('admin.products.index');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Toastr::error('Import failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Manual product announcement page (admin only).
     */
    public function announcementIndex(ProductAnnouncementDataTable $dataTable)
    {
        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        $productTypes = ProductType::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        $vendors = Vendor::where('status', 1)
            ->orderBy('shop_name')
            ->get(['id', 'shop_name']);

        return $dataTable->render('backend.product.announcement', compact('categories', 'productTypes', 'vendors'));
    }

    /**
     * Queue manual product announcement emails to all active users.
     */
    public function sendAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $productIds = collect($validated['product_ids'] ?? [])
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn ($id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $validProductIds = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($validProductIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid products selected.',
            ], 422);
        }

        $subject = trim((string) ($validated['subject'] ?? ''));
        $message = trim((string) ($validated['message'] ?? ''));

        DispatchProductAnnouncementChunksJob::dispatch(
            productIds: $validProductIds,
            source: 'manual',
            actorId: Auth::id() ? (int) Auth::id() : null,
            customSubject: $subject !== '' ? $subject : null,
            customMessage: $message !== '' ? $message : null,
            campaignId: 'manual-' . (string) Str::uuid()
        )->onConnection('database')->onQueue('mail-notifications');

        return response()->json([
            'success' => true,
            'message' => 'Announcement queued for ' . count($validProductIds) . ' selected products.',
        ]);
    }

    /**
     * @param mixed $rawRows
     * @return array<int, array<string, mixed>>
     */
    private function extractVariantRows($rawRows, bool $isUpdate): array
    {
        if (!is_array($rawRows)) {
            return [];
        }

        $rows = [];
        $seenPairs = [];

        foreach ($rawRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $colorId = isset($row['color_id']) && $row['color_id'] !== '' ? (int) $row['color_id'] : null;
            $sizeId = isset($row['size_id']) && $row['size_id'] !== '' ? (int) $row['size_id'] : null;

            if ($colorId === null && $sizeId === null) {
                continue;
            }

            $pairKey = ($colorId ?? 0) . '|' . ($sizeId ?? 0);
            if (isset($seenPairs[$pairKey])) {
                throw ValidationException::withMessages([
                    'variants' => 'Duplicate variant combination found. Please keep each color-size combination unique.',
                ]);
            }
            $seenPairs[$pairKey] = true;

            $prepared = [
                'color_id' => $colorId,
                'size_id' => $sizeId,
                'price' => max(0, (float) ($row['price'] ?? 0)),
                'outlet_price' => max(0, (float) ($row['outlet_price'] ?? 0)),
            ];

            if ($isUpdate) {
                if (!empty($row['id'])) {
                    $prepared['id'] = (int) $row['id'];
                }
                $prepared['current_stock'] = max(0, (float) ($row['current_stock'] ?? 0));
            } else {
                $prepared['qty'] = max(0, (int) ($row['qty'] ?? 0));
            }

            $rows[] = $prepared;
        }

        return $rows;
    }

    /**
     * Resolve variant display name from color and size IDs.
     */
    private function resolveVariantDisplayName(?int $colorId, ?int $sizeId): string
    {
        $colorName = '';
        $sizeName = '';

        if ($colorId) {
            $colorName = trim((string) optional(Color::find($colorId))->name);
        }
        if ($sizeId) {
            $sizeName = trim((string) optional(Size::find($sizeId))->name);
        }

        $name = trim(implode(' ', array_filter([$colorName, $sizeName])));
        return $name !== '' ? $name : 'Default';
    }

    /**
     * Normalize discount input.
     */
    private function normalizeDiscountInput(Request $request): array
    {
        $type = strtolower(trim((string) $request->input('discount_type', '')));
        $value = max(0, (float) $request->input('discount', 0));

        if (!in_array($type, ['flat', 'percent'], true) || $value <= 0) {
            return [
                'type' => null,
                'value' => 0.0,
            ];
        }

        if ($type === 'percent' && $value > 100) {
            throw new \InvalidArgumentException('Product discount percent cannot be greater than 100.');
        }

        return [
            'type' => $type,
            'value' => round($value, 2),
        ];
    }

    /**
     * Normalize VAT input.
     */
    private function normalizeVatInput(Request $request): array
    {
        $type = strtolower(trim((string) $request->input('vat_type', '')));
        $value = max(0, (float) $request->input('vat_value', 0));

        if (!in_array($type, ['flat', 'percent'], true) || $value <= 0) {
            return [
                'type' => null,
                'value' => null,
            ];
        }

        if ($type === 'percent' && $value > 100) {
            throw new \InvalidArgumentException('Product VAT percent cannot be greater than 100.');
        }

        return [
            'type' => $type,
            'value' => round($value, 2),
        ];
    }

    /**
     * @param array<int, int|string> $productIds
     */
    private function dispatchProductsPublishedEvent(array $productIds, string $source): void
    {
        $ids = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($productIds, static fn ($id): bool => (int) $id > 0)
        )));
        sort($ids);

        if (empty($ids)) {
            return;
        }

        $source = in_array($source, ['created', 'imported'], true) ? $source : 'created';
        $dispatchLockKey = 'product-announcement:dispatch:' . $source . ':' . sha1(json_encode($ids));

        if (!Cache::add($dispatchLockKey, 1, now()->addMinutes(10))) {
            return;
        }

        try {
            event(new ProductsPublished(
                productIds: $ids,
                source: $source,
                actorId: Auth::id() ? (int) Auth::id() : null
            ));
        } catch (\Throwable $e) {
            Log::warning('Unable to dispatch product announcement event', [
                'source' => $source,
                'product_ids_count' => count($ids),
                'error' => $e->getMessage(),
            ]);
        }
    }
}