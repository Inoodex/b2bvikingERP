<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductRequestDataTable;
use App\Http\Controllers\Controller;
use App\Mail\AdminRequestNotificationMail;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequestItem;
use App\Support\PiInfoSupport;
use App\Models\User;
use App\Mail\ProductRequestPiInvoiceReadyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Support\PdfImageHelper;
use App\Services\ApprovalService;


class ProductRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // No strict global middleware here because methods have internal checks
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ProductRequestDataTable $dataTable)
    {
        return $dataTable->render('backend.product-request.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Only admin can create requests for outlet/users from backend
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
             abort(403, 'Only admin can create product requests for outlets/users.');
        }

        $products = Product::where('status', 1)
            ->with(['variants.inventoryStocks', 'inventoryStocks'])
            ->get();
        
        $selectedIds = [];
        if ($request->has('ids')) {
            $selectedIds = explode(',', $request->ids);
        }

        $users = User::role(['Outlet User', 'User'])->where('status', 1)->orderBy('name', 'asc')->get();

        return view('backend.product-request.create', compact('products', 'users', 'selectedIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            abort(403);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'required_days' => 'nullable|integer|min:1',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $targetUser = User::role(['Outlet User', 'User'])
            ->where('status', 1)
            ->whereKey((int) $request->input('user_id'))
            ->first();

        if (!$targetUser) {
            Toastr::error('Please select a valid active Outlet/User.');
            return redirect()->back()->withInput();
        }

        $billingAddress = $this->resolveOrderBillingAddress($targetUser);
        $missingProfileFields = $this->missingOrderProfileFields($targetUser);
        if (!empty($missingProfileFields)) {
            $message = 'Selected user profile is incomplete. Missing: ' . implode(', ', $missingProfileFields) . '.';
            Toastr::error($message);
            return redirect()->back()
                ->withInput()
                ->withErrors(['user_id' => $message]);
        }

        DB::beginTransaction();
        try {
            $productRequest = new ProductRequest();

            $prefix = ($targetUser->hasRole('Outlet User') || $targetUser->hasRole('Outlet')) ? 'DS-REQ' : 'REQ';
            $productRequest->request_no = \App\Services\OrderNumberService::generate($prefix, \App\Models\ProductRequest::class);
            $productRequest->user_id = (int) $targetUser->id;
            $productRequest->status = 'approved';
            $productRequest->admin_note = 'Created by admin. Stock will be deducted only after Issue is created.';
            $productRequest->required_days = $request->required_days;
            $productRequest->note = $request->note;
            $productRequest->total_qty = 0; 
            $productRequest->save();

            $totalQty = 0;
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Price Hierarchy:
                // 1. Check variant outlet_price
                // 2. Check variant price
                // 3. Fallback to product outlet_price
                // 4. Fallback to product price
                $unitPrice = 0;
                
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ProductVariant::find($item['variant_id']);
                    if ($variant) {
                        if ($variant->outlet_price > 0) {
                            $unitPrice = $variant->outlet_price;
                        } elseif ($variant->price > 0) {
                            $unitPrice = $variant->price;
                        }
                    }
                }
                
                // Fallback to product-level prices if variant price not set
                if ($unitPrice <= 0) {
                    $unitPrice = $product->outlet_price > 0 ? $product->outlet_price : $product->price;
                }

                $subtotal = $item['qty'] * $unitPrice;
                
                $totalQty += $item['qty'];
                $totalAmount += $subtotal;

                ProductRequestItem::create([
                    'product_request_id' => $productRequest->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            $productRequest->total_qty = $totalQty;
            $productRequest->total_amount = $totalAmount;
            $productRequest->save();

            // Create Order record for Due Amount tracking
            // Use same number as request for clarity
            $orderNo = $productRequest->request_no;
            
            $order = \App\Models\Order::create([
                'order_no' => $orderNo,
                'user_id' => $targetUser->id,
                'status' => 'pending',
                'shipping_method' => 'admin_request',
                'billing_name' => $targetUser->name,
                'billing_email' => $targetUser->email,
                'billing_phone' => $targetUser->phone,
                'billing_address' => $billingAddress,
                'billing_outlet_name' => $targetUser->outlet_name,
                'subtotal_amount' => $totalAmount,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => $totalAmount,
                'due_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => 'pending',
                'placed_at' => now(),
            ]);


            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitPrice = 0;
                $variantLabel = null;
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ProductVariant::with(['color', 'size'])->find($item['variant_id']);
                    if ($variant) {
                        $variantLabel = trim(($variant->color->name ?? '') . ' ' . ($variant->size->name ?? ''));
                        $unitPrice = ($variant->outlet_price > 0) ? $variant->outlet_price : $variant->price;
                    }
                }
                if ($unitPrice <= 0) {
                    $unitPrice = ($product->outlet_price > 0) ? $product->outlet_price : $product->price;
                }

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'vendor_id' => $product->vendor_id,
                    'product_name' => $product->name,
                    'category_name' => $product->category->name ?? null,
                    'variant_label' => $variantLabel,
                    'product_image' => $product->thumb_image,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['qty'],
                    'line_total' => $item['qty'] * $unitPrice,
                ]);

            }

            // Link Order to Product Request
            $productRequest->order_id = $order->id;
            $productRequest->save();

            // Submit order for Multi-Level Approval
            $approvalService = app(ApprovalService::class);
            $approvalService->submitForApproval($order, (float)$order->total_amount);

            DB::commit();

            // Send Admin Notification Email
            try {
                Mail::to('ctpwh2026@gmail.com')
                    ->send(new AdminRequestNotificationMail($productRequest));
            } catch (\Exception $e) {
                Log::error('Failed to send admin product request notification: ' . $e->getMessage());
            }

            toastr()->success('Product Request and Order created successfully! Stock can be managed via Issue creation.');
            session()->flash('clear_request_basket', true);
            return redirect()->route('admin.orders.index');


        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    private function generateRequestNo($targetUser): string
{
    $isOutletUser = $targetUser->hasRole('Outlet User') || $targetUser->hasRole('Outlet');
    $prefix = $isOutletUser ? 'DS-REQ' : 'REQ';

    $year  = now()->format('Y');
    $month = now()->format('m');

    return DB::transaction(function () use ($prefix, $year, $month) {

        $monthPrefix = $prefix . '-' . $year . $month . '-';

        $count =ProductRequest::whereYear('created_at', $year)
                      ->whereMonth('created_at', $month)
                      ->where('request_no', 'LIKE', $monthPrefix . '%')
                      ->lockForUpdate()
                      ->distinct('request_no')
                      ->count('request_no');

        $next = $count + 1;

        return $monthPrefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    });
}

    private function missingOrderProfileFields(User $user): array
    {
        $requiredFields = [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
        ];

        $missing = [];
        foreach ($requiredFields as $field => $label) {
            $value = trim((string) ($user->{$field} ?? ''));
            if ($value === '') {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    private function resolveOrderBillingAddress(User $user): string
    {
        $candidates = [
            $user->address,
            $user->outlet_name,
            $user->name,
            'N/A',
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return 'N/A';
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productRequest = ProductRequest::with(['user', 'order.payments', 'items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($id);

        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403, 'Unauthorized access to this product request.');
        }

        foreach($productRequest->items as $item) {
             $stock = InventoryStock::where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->where('outlet_id', 1)
                ->first();
             $item->current_stock = $stock ? $stock->quantity : 0;
        }

        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        return view('backend.product-request.show', compact('productRequest', 'piInfo', 'piTotals', 'hasSavedPiInfo'));
    }

    /**
     * View Request Invoice (HTML)
     */
    public function viewInvoice($id)
    {
        $productRequest = ProductRequest::with(['user', 'order', 'items.product.unit', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403);
        }

        $settings = \App\Models\GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        return view('backend.product-request.invoice', compact('productRequest', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo'));
    }

    /**
     * View Request PI Invoice (HTML)
     */
    public function piInvoice($id)
    {
        $productRequest = ProductRequest::with([
            'user',
            'order',
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
        ])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403);
        }

        $settings = \App\Models\GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        $downloadUrl = route('admin.product-requests.pi-invoice.download', $productRequest->id);

        return view('backend.product-request.pi_invoice', compact('productRequest', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'downloadUrl'));
    }

    /**
     * Generate Request PDF
     */
    public function printPdf($id)
    {
        // Increase resources for PDF generation to prevent 503 errors on live servers
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $productRequest = ProductRequest::with(['user', 'order', 'items.product.unit', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403);
        }

        $settings = \App\Models\GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        // Configure DomPDF wrapper for better performance
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif'
        ])->loadView('backend.product-request.print_pdf', compact('productRequest', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo'));

        $filename = 'request-' . $productRequest->request_no . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Download Request PI Invoice (PDF)
     */
    public function downloadPiInvoice($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $productRequest = ProductRequest::with([
            'user',
            'order',
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
        ])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403);
        }

        $settings = \App\Models\GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        // Optimize logo
        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 40);

        // Optimize product images
        foreach ($productRequest->items as $item) {
            if ($item->product && $item->product->thumb_image) {
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 60, 60);
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif'
        ])->loadView('backend.product-request.pi_invoice', compact('productRequest', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo') + ['isPdf' => true]);

        return $pdf->download('pi-invoice-' . $productRequest->request_no . '.pdf');
    }

    /**
     * Download customer invoice for request as PDF.
     */
    public function downloadCustomerInvoice($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $productRequest = ProductRequest::with(['user', 'order.items.product', 'order.items.variant.color', 'order.items.variant.size'])->findOrFail($id);
        
        if (!$productRequest->order) {
            toastr()->error('No linked order found for this request. Please contact admin.');
            return redirect()->back();
        }

        $order = $productRequest->order;
        $settings = \App\Models\GeneralSetting::first();

        // Optimize logo for PDF
        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 120, 30);

        // Optimize product images for PDF (smaller for customer invoice)
        foreach ($order->items as $item) {
            $item->optimized_image = PdfImageHelper::optimize($item->product_image, 60, 60);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('backend.orders.customer_invoice', compact('order', 'settings'));
        return $pdf->download('customer-invoice-' . $order->order_no . '.pdf');
    }

    /**
     * Save manual PI/CTN information for a request.
     */
    public function savePiInfo(Request $request, $id)
    {
        $productRequest = ProductRequest::with('items')->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Product Requests')) {
            abort(403);
        }

        $validated = $request->validate([
            'pi_type' => 'required|in:simple,advanced',
            'shipment_qty' => 'required|integer|min:0',
            'shipment_date' => 'nullable|date',
            'packing_note' => 'nullable|string|max:2000',
            'pi_rows' => 'nullable|array',
            'pi_rows.*.ordered_qty' => 'nullable|integer|min:0',
            'pi_rows.*.ctn_no' => 'nullable|string|max:100',
            'pi_rows.*.ctn_size' => 'nullable|string|max:100',
            'pi_rows.*.pcs_per_ctn' => 'nullable|integer|min:0',
            'pi_rows.*.ctn_qty' => 'nullable|integer|min:0',
            'pi_rows.*.total_pcs' => 'nullable|integer|min:0',
            'pi_rows.*.nw_kg' => 'nullable|numeric|min:0',
            'pi_rows.*.gw_kg' => 'nullable|numeric|min:0',
            'pi_rows.*.note' => 'nullable|string|max:500',
            'advanced_blocks' => 'nullable|array',
            'advanced_blocks.*.block_key' => 'nullable|string|max:100',
            'advanced_blocks.*.product_id' => 'nullable|integer',
            'advanced_blocks.*.title' => 'nullable|string|max:255',
            'advanced_blocks.*.color_label' => 'nullable|string|max:255',
            'advanced_blocks.*.image' => 'nullable|string|max:500',
            'advanced_blocks.*.variant_headers_csv' => 'nullable|string|max:1000',
            'advanced_blocks.*.color_headers_csv' => 'nullable|string|max:500',
            'advanced_blocks.*.size_headers_csv' => 'nullable|string|max:500',
            'advanced_blocks.*.rows' => 'nullable|array',
            'advanced_blocks.*.ctn_size' => 'nullable|string|max:100',
            'advanced_blocks.*.rows.*.ctn_qty' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.ctn_no' => 'nullable|string|max:100',
            'advanced_blocks.*.rows.*.variants' => 'nullable|array',
            'advanced_blocks.*.rows.*.variants.*' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.colors' => 'nullable|array',
            'advanced_blocks.*.rows.*.colors.*' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.sizes' => 'nullable|array',
            'advanced_blocks.*.rows.*.sizes.*' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.pcs' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.total_pcs' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.nw_kg' => 'nullable|numeric|min:0',
            'advanced_blocks.*.rows.*.gw_kg' => 'nullable|numeric|min:0',
        ]);

        $productRequest->pi_info = PiInfoSupport::sanitizePayload($validated);
        $productRequest->save();

        $path = 'invoices/pi-invoice-' . $productRequest->request_no . '.pdf';
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->notifyPiReady($productRequest);

        Toastr::success('PI info saved successfully!');
        return redirect()->route('admin.product-requests.show', $productRequest->id);
    }

    /**
     * Update the status of the request.
     */
    /**
     * Update the status of the request.
     */
    public function updateStatus(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Only Admin/Manager can update status
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Product Requests')) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,shipped,completed',
            'admin_note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $productRequest->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note
            ]);

            // Sync Order status if linked
            if ($productRequest->order) {
                // Determine order status based on request status
                // Laravel Order statuses: pending, approved, processing, shipped, completed, cancelled, rejected
                $orderStatus = $request->status;
                $productRequest->order->update(['status' => $orderStatus]);
            }

            
            DB::commit();
            toastr()->success('Product Request updated successfully!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Authorization: Manager can delete anything, User can only delete own pending requests
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Product Requests') && ($productRequest->user_id != Auth::id() || $productRequest->status !== 'pending')) {
             return response(['status' => 'error', 'message' => 'Unauthorized or request already processed']);
        }

        $productRequest->delete(); // Items deleted by cascade
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    private function notifyPiReady(ProductRequest $productRequest): void
    {
        $productRequest->loadMissing(['user', 'order']);

        $recipient = $productRequest->order?->pi_email
            ?: $productRequest->order?->billing_email
            ?: ($productRequest->user?->email ?? null);

        if (!$recipient) {
            return;
        }

        $attachPdf = (bool) config('mail.attach_pi_pdf', true);

        try {
            Mail::to($recipient)->send(new ProductRequestPiInvoiceReadyMail(
                $productRequest,
                route('product-requests.pi-invoice', $productRequest->id),
                route('product-requests.pi-invoice.download', $productRequest->id),
                $attachPdf
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to send PI invoice email for product request.', [
                'product_request_id' => $productRequest->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
