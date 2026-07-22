<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\AdminIssueNotificationMail;
use App\Models\GeneralSetting;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductVariant;
use App\Models\Color;
use App\Models\Size;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\PdfImageHelper;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;

class IssueController extends Controller
{
    private function resolveVariantColorName(?ProductVariant $variant): ?string
    {
        if (!$variant) {
            return null;
        }

        $relationColor = $variant->getRelation('color');
        if (is_object($relationColor) && isset($relationColor->name)) {
            return $relationColor->name;
        }

        if (!empty($variant->color_id)) {
            $name = Color::query()->whereKey($variant->color_id)->value('name');
            if ($name) {
                return $name;
            }
        }

        return is_string($variant->color) && $variant->color !== '' ? $variant->color : null;
    }

    private function resolveVariantSizeName(?ProductVariant $variant): ?string
    {
        if (!$variant) {
            return null;
        }

        $relationSize = $variant->getRelation('size');
        if (is_object($relationSize) && isset($relationSize->name)) {
            return $relationSize->name;
        }

        if (!empty($variant->size_id)) {
            $name = Size::query()->whereKey($variant->size_id)->value('name');
            if ($name) {
                return $name;
            }
        }

        return is_string($variant->size) && $variant->size !== '' ? $variant->size : null;
    }

    public function index()
    {
        $issues = Issue::with('outlet')->latest()->get();
        return view('backend.issue.index', compact('issues'));
    }

    public function create(Request $request)
    {
        $products = Product::where('status', 1)
        ->with(['variants.color', 'variants.size', 'variants.inventoryStocks', 'inventoryStocks'])
        ->get();
        //when no need to check status: 
        //  $products = Product::with(['variants.color', 'variants.size', 'variants.inventoryStocks', 'inventoryStocks'])->get();
            
        // Fetch requests that are approved
        $productRequests = ProductRequest::with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();

        $frontendOrders = Order::with('user')
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
            ->latest()
            ->get();

        $requestId = $request->query('request_id');
        $orderId = $request->query('order_id');
        $outletUsers = User::role(['Outlet User', 'User'])->get();
        $selectedOrder = null;
        $isOrderSource = !empty($orderId);

        if ($isOrderSource) {
            $selectedOrder = Order::with('user')->find($orderId);
        }
             
        return view('backend.issue.create', compact(
            'products',
            'productRequests',
            'frontendOrders',
            'requestId',
            'orderId',
            'outletUsers',
            'selectedOrder',
            'isOrderSource'
        ));
    }

    public function getRequestItems(Request $request)
    {
        if ($request->filled('order_id')) {
            $order = Order::with(['items.product', 'items.variant.color', 'items.variant.size'])
                ->findOrFail($request->order_id);

            $items = $order->items->map(function ($item) use ($order) {
                $stock = InventoryStock::where([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'outlet_id' => 1,
                ])->first();

                // Calculate how much has already been issued for this specific order and product/variant
                $issuedQty = IssueItem::whereHas('issue', function($q) use ($order) {
                    $q->where('order_id', $order->id);
                })->where([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                ])->sum('quantity');

                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?: ($item->product->name ?? 'Deleted Product'),
                    'thumb_image' => $item->product_image ?: ($item->product->thumb_image ?? null),
                    'variant_id' => $item->variant_id,
                    'variant_name' => $item->variant ? $item->variant->name : ($item->variant_label ?: null),
                    'color_name' => $this->resolveVariantColorName($item->variant),
                    'size_name' => $this->resolveVariantSizeName($item->variant),
                    'requested_qty' => (int) $item->quantity,
                    'issued_qty' => (int) $issuedQty,
                    'remaining_qty' => (int) $item->quantity - (int) $issuedQty,
                    'unit_price' => (float) $item->unit_price,
                    'available_stock' => $stock ? (int) $stock->quantity : 0,
                ];
            });

            return response()->json([
                'items' => $items,
                'user_id' => $order->user_id,
                'source_type' => 'order',
                'source_ref' => $order->order_no,
            ]);
        }

        $productRequest = ProductRequest::with(['items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($request->request_id);
        
        $items = $productRequest->items->map(function($item) {
            // Get current warehouse stock for validation in UI
            $stock = InventoryStock::where([
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'outlet_id' => 1
            ])->first();

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : 'Deleted Product',
                'thumb_image' => $item->product ? $item->product->thumb_image : null,
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant ? $item->variant->name : null,
                'color_name' => $this->resolveVariantColorName($item->variant),
                'size_name' => $this->resolveVariantSizeName($item->variant),
                'requested_qty' => $item->qty,
                'unit_price' => $item->unit_price,
                'available_stock' => $stock ? $stock->quantity : 0,
            ];
        });

        return response()->json([
            'items' => $items,
            'user_id' => $productRequest->user_id,
            'source_type' => 'request',
            'source_ref' => $productRequest->request_no,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'outlet_id' => 'required|exists:users,id',
            'product_request_id' => 'nullable|exists:product_requests,id',
            'order_id' => 'nullable|exists:orders,id',
            'note' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
            $sourceNote = null;
            if ($request->filled('product_request_id')) {
                $requestRef = ProductRequest::find($request->product_request_id);
                $sourceNote = $requestRef ? ('Source Request: ' . $requestRef->request_no) : null;
            } elseif ($request->filled('order_id')) {
                $orderRef = Order::find($request->order_id);
                $sourceNote = $orderRef ? ('Source Order: ' . $orderRef->order_no) : null;
            }

            $note = trim((string) $request->note);
            if ($sourceNote) {
                $note = $note !== '' ? ($note . ' | ' . $sourceNote) : $sourceNote;
            }

            $issue = Issue::create([
                'issue_no' => 'ISS-' . strtoupper(uniqid()),
                'product_request_id' => $request->product_request_id,
                'order_id' => $request->order_id,
                'outlet_id' => $request->outlet_id,
                'status' => 'confirmed',
                'total_qty' => collect($request->items)->sum('quantity'),
                'note' => $note !== '' ? $note : null,
            ]);

            // Fallback: ensure order_id is persisted even if mass-assignment was blocked
            if ($request->filled('order_id') && (!$issue->order_id || $issue->order_id != $request->order_id)) {
                $issue->order_id = $request->order_id;
                $issue->save();
            }

            // If this issue is linked to a product request, update its status
            if ($request->product_request_id) {
                $productRequest = ProductRequest::find($request->product_request_id);
                if ($productRequest) {
                    $productRequest->update([
                        'status' => 'completed',
                        'admin_note' => $productRequest->admin_note . "\nStock Issued: " . $issue->issue_no
                    ]);
                }
            }

            if ($request->order_id) {
                $order = Order::find($request->order_id);
                if ($order && !in_array(strtolower((string) $order->status), ['cancelled', 'rejected'], true)) {
                    $order->update(['status' => 'completed']);
                }
            }

            foreach ($request->items as $item) {
                // Resolve unit_price: linked order → order_items.unit_price, standalone → products.price
                $product = Product::find($item['product_id']);
                if ($request->filled('order_id') && $request->order_id) {
                    $orderItem = \App\Models\OrderItem::where('order_id', $request->order_id)
                        ->where('product_id', $item['product_id'])
                        ->where(function ($q) use ($item) {
                            $q->where('variant_id', $item['variant_id'] ?? null)
                              ->orWhereNull('variant_id');
                        })
                        ->first();
                    $unitPrice = $orderItem ? $orderItem->unit_price : ($product->price ?? $product->purchase_price ?? 0);
                } else {
                    $unitPrice = $product->price ?? $product->purchase_price ?? 0;
                }

                // 1. Create Issue Item
                IssueItem::create([
                    'issue_id' => $issue->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?: null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                ]);

                // 2. Check main warehouse (outlet 1) stock
                $mainStock = InventoryStock::where([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?: null,
                    'outlet_id' => 1
                ])->first();

                $availableQty = $mainStock ? $mainStock->quantity : 0;

                if ($availableQty < $item['quantity']) {
                    $productName = \App\Models\Product::find($item['product_id'])->name ?? 'Product #'.$item['product_id'];
                    throw new \Exception("Insufficient stock for '{$productName}'. Available: {$availableQty}, Required: {$item['quantity']}");
                }

                // 3. Deduct from main warehouse (outlet 1)
                $mainStock->decrement('quantity', $item['quantity']);

                // 4. Create Stock Ledger Entry (outlet_id = user for filter, balance_qty = main warehouse stock)
                StockLedger::create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'outlet_id' => $issue->outlet_id,
                    'reference_type' => 'issue',
                    'reference_id' => $issue->id,
                    'in_qty' => 0,
                    'out_qty' => $item['quantity'],
                    'balance_qty' => $mainStock->quantity,
                    'date' => now()
                ]);
            }

            if ($request->order_id) {
                $order = Order::find($request->order_id);
                if ($order) {
                    $order->reconcileTotals();
                }
            }
            
            // try {
            //     $this->generateInvoice($issue);
            // } catch (\Exception $e) {
            //     \Illuminate\Support\Facades\Log::error('Invoice Generation Failed: ' . $e->getMessage());
            // }

            // Send Admin Notification Email
            try {
                \Illuminate\Support\Facades\Mail::to('ctpwh2026@gmail.com')
                    ->send(new AdminIssueNotificationMail($issue));
            } catch (\Exception $e) {
               Log::error('Failed to send admin issue notification: ' . $e->getMessage());
            }
        });
        } catch (\Exception $e) {
            Log::error('Issue creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.issues.index')->with('success', 'Stock Issued Successfully!');
    }

    public function show($id)
    {
        $issue = Issue::with(['items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        return view('backend.issue.show', compact('issue'));
    }

    public function generateInvoice(Issue $issue)
    {
         $issue->load(['items.product', 'items.variant.color', 'items.variant.size', 'outlet', 'productRequest']);
         $settings = GeneralSetting::first();
         
         // Optimize logo
         $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
         $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 38);

         // Optimize product images
         foreach ($issue->items as $item) {
             if ($item->product && $item->product->thumb_image) {
                 $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 60, 60);
             }
         }

         $pdf = Pdf::loadView('backend.pdf.issue-invoice', array_merge(compact('issue', 'settings'), ['is_pdf' => true]));
         $fileName = 'issue_invoice_' . $issue->issue_no . '.pdf';
         $path = 'invoices/' . $fileName;
         
         Storage::disk('public')->put($path, $pdf->output());
         
         $issue->update(['invoice_path' => $path]);
    }

    public function viewInvoice($id)
    {
        $issue = Issue::with(['items.product', 'items.variant.color', 'items.variant.size', 'outlet', 'productRequest'])->findOrFail($id);
        $settings = GeneralSetting::first();

        // Optimize logo
        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 160, 38);

        // Optimize product images
        foreach ($issue->items as $item) {
            if ($item->product && $item->product->thumb_image) {
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 60, 60);
            }
        }
        
        // Return HTML view for preview
        return view('backend.pdf.issue-invoice', array_merge(compact('issue', 'settings'), ['is_pdf' => false]));
    }

    public function downloadInvoice($id)
    {
        $issue = Issue::findOrFail($id);

        // Delete old PDF if exists
        $fileName = 'issue_invoice_' . $issue->issue_no . '.pdf';
        $path = 'invoices/' . $fileName;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Dispatch job to queue
        \App\Jobs\GenerateIssuePdfJob::dispatch($issue->id, \Illuminate\Support\Facades\Auth::id());

        Toastr::info('Issue Invoice is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();
    }
}
