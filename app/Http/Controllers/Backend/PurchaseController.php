<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseAttachment;
use App\Models\PurchaseDetail;
use App\Models\StockLedger;
use App\Models\Vendor;
use App\Models\PricingRule;
use App\Support\StoredFileSupport;
use App\Support\AuditLogSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\Support\PdfImageHelper;
use App\Exports\PurchasesExport;
use App\Exports\PurchaseOrderExport;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with(['vendor', 'user', 'details', 'attachments'])->orderBy('id', 'desc')->get(); // Using get() for simple list first, or DataTable later if requested in plan
        return view('backend.purchase.index', compact('purchases'));
    }

    public function exportExcel()
    {
        return Excel::download(new PurchasesExport, 'purchases-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function downloadExcel(string $id)
    {
        return Excel::download(
            new PurchaseOrderExport((int) $id),
            'purchase-invoice-' . $id . '.xlsx'
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        
        $vendors = Vendor::where('status', 1)->get();
        // Passing products for JS selection
        $products = Product::where('status', 1)->with('variants.color', 'variants.size')->orderByDesc('id')->get(); 

        $pricingRules = PricingRule::where('status', 1)->orderByDesc('is_default')->orderBy('name')->get();
        $defaultPricingRuleId = optional($pricingRules->firstWhere('is_default', true))->id;
        
        // Fetch Bookings (Only those not fully purchased? For now, only 'pending' bookings)
        $bookings = Booking::with('vendor')
                    ->where('status', 'pending')
                    ->select(
                        DB::raw('MIN(id) as id'),
                        'booking_no', 
                        'vendor_id', 
                        DB::raw('count(product_id) as product_count')
                    )
                    ->groupBy('booking_no', 'vendor_id')
                    ->orderByDesc('id')
                    ->get();

        // Handle selected product IDs from low stock alert
        $selectedIds = [];
        if ($request->has('ids')) {
            $selectedIds = explode(',', $request->ids);
        }

        return view('backend.purchase.create', compact('vendors', 'products', 'bookings', 'pricingRules', 'defaultPricingRuleId', 'selectedIds'));
    }

    public function getBookingDetails(Request $request) {
        $booking = Booking::with(['product', 'vendor', 'unit'])->findOrFail($request->id);
        
        // Fetch all products that share the same booking_no
        $bookings = Booking::with(['product', 'vendor', 'unit'])
                    ->where('booking_no', $booking->booking_no)
                    ->get();
        
        // Add shipping_method to the response (from the first booking in the group)
        $response = $bookings->toArray();
        if (count($response) > 0 && isset($response[0]['shipping_method'])) {
            foreach ($response as &$item) {
                $item['shipping_method'] = $response[0]['shipping_method'];
            }
        }
                    
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'vendor_id' => 'required',
            'date' => 'required|date',
            'pricing_rule_id' => 'nullable|exists:pricing_rules,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'invoice_attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,xlsx,xls|max:51200', // Max 50MB
        ]);

        DB::beginTransaction();
        try {
            $purchase = new Purchase();
            $purchase->invoice_no = 'INV-' . mt_rand(100000, 999999);
            $purchase->vendor_id = $request->vendor_id;
            $purchase->booking_id = $request->booking_id; // Save Booking ID
            $purchase->user_id = Auth::id(); // Track Creator
            $purchase->date = $request->date;
            $purchase->note = $request->note;
            $purchase->shipping_method = $request->shipping_method;
            $purchase->material_cost = $request->material_cost ?? 0;
            $purchase->transport_cost = $request->transport_cost ?? 0;
            $purchase->tax = $request->tax ?? 0;
            $purchase->total_amount = 0; // Will calculate
            $purchase->status = 1;

            $storedInvoiceAttachment = null;

            // Handle Invoice Attachment Upload
            if ($request->hasFile('invoice_attachment')) {
                $file = $request->file('invoice_attachment');
                $filename = 'invoice_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = StoredFileSupport::storePrivateFile($file, 'attachments/purchases', $filename);
                
                $purchase->invoice_attachment = $path;
                $storedInvoiceAttachment = [
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ];
            }

            $purchase->save();

            if ($storedInvoiceAttachment) {
                $purchase->attachments()->create([
                    'file_path' => $storedInvoiceAttachment['file_path'],
                    'original_name' => $storedInvoiceAttachment['original_name'],
                    'mime_type' => $storedInvoiceAttachment['mime_type'],
                    'file_size' => $storedInvoiceAttachment['file_size'],
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // Calculate costs in System Currency (Input is Vendor Currency)
            $vendor = Vendor::findOrFail($request->vendor_id);
            $rate = $vendor->currency_rate > 0 ? $vendor->currency_rate : 1;

            $totalAmount = 0;

            $rule = null;
            if ($request->pricing_rule_id) {
                $rule = PricingRule::where('status', 1)->find($request->pricing_rule_id);
            }

            foreach ($request->items as $item) {
                $qty = $item['qty'] > 0 ? $item['qty'] : 1;
                // Get cost breakdown components (these are in system currency)
                // Raw material cost acts as the base cost (includes converted vendor cost)
                $rawMaterial = ($item['raw_material_cost'] ?? 0);
                $tax = ($item['tax_cost'] ?? 0);
                $transport = ($item['transport_cost'] ?? 0);
                
                // Landed cost (per unit) = rawMaterial + tax + transport
                $itemUnitCost = $rawMaterial + $tax + $transport;
                
                // Subtotal = (Raw Material + Tax + Transport) * Qty
                $subTotal = $itemUnitCost * $qty;
                $totalAmount += $subTotal;

                // Create Detail
                $detail = new PurchaseDetail();
                $detail->purchase_id = $purchase->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit_cost = $itemUnitCost;
                $detail->unit_cost_vendor = $item['unit_cost'] ?? 0;
                $detail->raw_material_cost = $rawMaterial;
                $detail->tax_cost = $tax;
                $detail->transport_cost = $transport;
                $detail->total = $subTotal;
                
                // Save & Standardize Variant Info
                $vInfo = null;
                if(isset($item['variant_info']) && !empty($item['variant_info'])) {
                    $vInfo = is_string($item['variant_info']) ? json_decode($item['variant_info'], true) : $item['variant_info'];
                    $detail->variant_info = $vInfo; 
                }
                
                $detail->save();

                // Update Product Costs and Prices
                $product = Product::findOrFail($item['product_id']);
                
                // Store the total landed cost (system currency) as the purchase price
                $product->purchase_price = $itemUnitCost;
                
                // Update local costs to the product record
                $product->raw_material_cost = $rawMaterial;
                $product->tax = $tax;
                $product->transport_cost = $transport;
                
                if ($rule) {
                    $product->price = round($itemUnitCost * (float)$rule->sale_multiplier, 2);
                    $product->outlet_price = round($itemUnitCost * (float)$rule->outlet_multiplier, 2);
                } else {
                    if (isset($item['sale_price'])) {
                        $product->price = $item['sale_price'];
                    }
                    if (isset($item['outlet_price'])) {
                        $product->outlet_price = $item['outlet_price'];
                    }
                }
                
                $product->save();
                
                // Update main stock
                // REDUNDANT - Handled by InventoryStock
                // $product->increment('qty', $item['qty']);
                
                // Update Stock (Variants)
                // Update Stock (Variants)
                if($vInfo && is_array($vInfo)) {
                    $processedVariants = [];
                    // Handle "Old Format" single variant {variant: "Name"}
                    if(isset($vInfo['variant'])) {
                        $variantName = $vInfo['variant'];
                        $variantQty = $item['qty'];
                        
                        $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                    ->where('name', $variantName)
                                    ->first();
                        if($pVariant && !in_array($pVariant->id, $processedVariants)) {
                            // REDUNDANT - Handled by InventoryStock
                            // $pVariant->increment('qty', $variantQty);
                            $processedVariants[] = $pVariant->id;

                            // INV PLANE: InventoryStock
                            $stock = \App\Models\InventoryStock::firstOrCreate([
                                'product_id' => $item['product_id'],
                                'variant_id' => $pVariant->id,
                                'outlet_id' => 1 // Default
                            ]);
                            $stock->increment('quantity', $variantQty);

                            // INV PLANE: StockLedger
                            \App\Models\StockLedger::create([
                                'product_id' => $item['product_id'],
                                'variant_id' => $pVariant->id,
                                'outlet_id' => 1,
                                'reference_type' => 'purchase',
                                'reference_id' => $purchase->id,
                                'in_qty' => $variantQty,
                                'out_qty' => 0,
                                'balance_qty' => $stock->quantity, // Post-increment
                                'date' => $request->date
                            ]);
                            
                            // INV PLANE: Update Detail variant_id
                            $detail->variant_id = $pVariant->id;
                            $detail->save();
                        }
                    } else {
                        // Handle "New Aggregated Format" {"Name": Qty, "Name2": Qty}
                        foreach($vInfo as $vName => $vQty) {
                            // 1. Try Exact Match
                            $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                        ->where('name', trim($vName))
                                        ->first();

                            // 2. Try Cleaning Prefixes (Color:, Size:)
                            if (!$pVariant) {
                                $cleanName = preg_replace('/(Color|Size):\s*/i', '', $vName);
                                $cleanName = trim($cleanName);
                                
                                if ($cleanName !== $vName) {
                                     $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                            ->where('name', $cleanName)
                                            ->first();
                                }
                            }

                            // 3. Try Legacy Cleanup (Hyphens to Spaces) - Only if still not found
                            if (!$pVariant && isset($cleanName)) {
                                 $cleanNameLegacy = preg_replace('/\s*-\s*/', ' ', $cleanName);
                                 $cleanNameLegacy = trim($cleanNameLegacy);
                                 
                                 if ($cleanNameLegacy !== $cleanName) {
                                     $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                            ->where('name', $cleanNameLegacy)
                                            ->first();
                                 }
                            }
                            
                            if($pVariant) {
                                // REDUNDANT - Handled by InventoryStock
                                // $pVariant->increment('qty', $vQty);
                                
                                // INV PLANE: InventoryStock
                                $stock = \App\Models\InventoryStock::firstOrCreate([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => $pVariant->id,
                                    'outlet_id' => 1 // Default
                                ]);
                                $stock->increment('quantity', $vQty);
    
                                // INV PLANE: StockLedger
                                \App\Models\StockLedger::create([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => $pVariant->id,
                                    'outlet_id' => 1,
                                    'reference_type' => 'purchase',
                                    'reference_id' => $purchase->id,
                                    'in_qty' => $vQty,
                                    'out_qty' => 0,
                                    'balance_qty' => $stock->quantity, // Post-increment
                                    'date' => $request->date
                                ]);

                                // Note: PurchaseDetail structure assumes one variant per line often, 
                                // but if aggregated, we might have issues linking single detail to multiple variant ledgers.
                                // For now, we update logic, but ideal structure is 1 line = 1 variant.
                                // If detail->variant_id is single, we can only set one. 
                                // Assuming simplest case: likely one variant dominant or split lines.
                                // We will update variant_id if it's the first one found, for trace.
                                if(!$detail->variant_id) {
                                    $detail->variant_id = $pVariant->id;
                                    $detail->save();
                                }
                            } else {
                                // Fallback: If variant not found by name, assign to main product stock (No Variant)
                                // This ensures stock is not lost if name matching fails
                                $stock = \App\Models\InventoryStock::firstOrCreate([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => null,
                                    'outlet_id' => 1
                                ]);
                                $stock->increment('quantity', $vQty);

                                // Ledger Fallback
                                \App\Models\StockLedger::create([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => null,
                                    'outlet_id' => 1,
                                    'reference_type' => 'purchase',
                                    'reference_id' => $purchase->id,
                                    'in_qty' => $vQty,
                                    'out_qty' => 0,
                                    'balance_qty' => $stock->quantity,
                                    'date' => $request->date
                                ]);
                            }
                        }
                    }
                } else {
                    // No Variant Info - Product Level Stock Logic
                    // INV PLANE: InventoryStock (No Variant)
                    $stock = \App\Models\InventoryStock::firstOrCreate([
                        'product_id' => $item['product_id'],
                        'variant_id' => null,
                        'outlet_id' => 1 // Default
                    ]);
                    $stock->increment('quantity', $item['qty']);

                    // INV PLANE: StockLedger
                    \App\Models\StockLedger::create([
                        'product_id' => $item['product_id'],
                        'variant_id' => null,
                        'outlet_id' => 1,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'in_qty' => $item['qty'],
                        'out_qty' => 0,
                        'balance_qty' => $stock->quantity,
                        'date' => $request->date
                    ]);
                }
            }

            // The totalAmount already includes material, transport, and tax distributed at row level
            $purchase->total_amount = $totalAmount;
            $purchase->paid_amount = 0;
            $purchase->due_amount = $totalAmount;
            $purchase->payment_status = $totalAmount > 0 ? 'pending' : 'paid';
            $purchase->save();

            // Automate Booking Completion for the entire group
            if($purchase->booking_id) {
                $targetBooking = \App\Models\Booking::find($purchase->booking_id);
                if($targetBooking) {
                    \App\Models\Booking::where('booking_no', $targetBooking->booking_no)->update(['status' => 'complete']);
                }
            }

            AuditLogSupport::log([
                'vendor_id' => $purchase->vendor_id,
                'module' => 'purchases',
                'action' => 'purchase_created',
                'entity_type' => 'purchase',
                'entity_id' => $purchase->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Purchase created.',
                'new_values' => [
                    'vendor_id' => $purchase->vendor_id,
                    'booking_id' => $purchase->booking_id,
                    'date' => $purchase->date,
                    'total_amount' => (float) $purchase->total_amount,
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'payment_status' => (string) $purchase->payment_status,
                    'item_count' => count($request->items ?? []),
                    'attachment_count' => $purchase->attachments()->count(),
                ],
            ]);

            DB::commit();
            
            Toastr::success('Purchase Created Successfully!');
            return redirect()->route('admin.purchases.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['vendor', 'user', 'details.product', 'attachments', 'payments.receipts'])->findOrFail($id);
        return view('backend.purchase.show', compact('purchase'));
    }

    /**
     * View invoice for purchase
     */
    public function viewInvoice(string $id)
    {
        $purchase = Purchase::with(['vendor', 'user', 'details.product', 'attachments'])->findOrFail($id);
        $settings = \App\Models\GeneralSetting::first();
        return view('backend.purchase.invoice', compact('purchase', 'settings'));
    }

    /**
     * Download PDF for purchase
     */
    public function downloadPdf(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        $path = 'purchases/purchase_' . $purchase->invoice_no . '.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePurchasePdfJob::dispatch($purchase->id, \Illuminate\Support\Facades\Auth::id());
        
        Toastr::info('Purchase PDF is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();
    }

    /**
     * Upload invoice attachments from purchase listing or details page.
     */
    public function uploadAttachments(Request $request, string $id)
    {
        $purchase = Purchase::findOrFail($id);
        $storedPaths = [];

        $request->validate([
            'invoice_attachments' => 'required|array|min:1',
            'invoice_attachments.*' => 'file|mimes:jpeg,png,jpg,pdf,xlsx,xls|max:5120',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->file('invoice_attachments', []) as $file) {
                $filename = 'invoice_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = StoredFileSupport::storePrivateFile($file, 'attachments/purchases/' . $purchase->id, $filename);
                $storedPaths[] = $path;

                $purchase->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);

                // Keep legacy single attachment populated for backward compatibility.
                if (empty($purchase->invoice_attachment)) {
                    $purchase->invoice_attachment = $path;
                    $purchase->save();
                }

                $latestAttachment = $purchase->attachments()->latest('id')->first();

                AuditLogSupport::log([
                    'vendor_id' => $purchase->vendor_id,
                    'module' => 'purchases',
                    'action' => 'purchase_attachment_uploaded',
                    'entity_type' => 'purchase_attachment',
                    'entity_id' => $latestAttachment?->id,
                    'reference_no' => $purchase->invoice_no,
                    'description' => 'Purchase attachment uploaded.',
                    'new_values' => [
                        'purchase_id' => $purchase->id,
                        'attachment_id' => $latestAttachment?->id,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                    ],
                ]);
            }

            DB::commit();
            Toastr::success('Invoice attachment uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($storedPaths as $filePath) {
                StoredFileSupport::delete($filePath);
            }
            Toastr::error('Upload failed: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Delete a single purchase attachment.
     */
    public function deleteAttachment(string $id, string $attachmentId)
    {
        $purchase = Purchase::findOrFail($id);
        $attachment = PurchaseAttachment::where('purchase_id', $purchase->id)->findOrFail($attachmentId);

        DB::beginTransaction();
        try {
            $filePath = $attachment->file_path;
            $legacyMatchesThis = $purchase->invoice_attachment === $filePath;

            AuditLogSupport::log([
                'vendor_id' => $purchase->vendor_id,
                'module' => 'purchases',
                'action' => 'purchase_attachment_deleted',
                'entity_type' => 'purchase_attachment',
                'entity_id' => $attachment->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Purchase attachment deleted.',
                'old_values' => [
                    'purchase_id' => $purchase->id,
                    'attachment_id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'file_path' => $attachment->file_path,
                ],
            ]);

            StoredFileSupport::delete($filePath);

            $attachment->delete();

            if ($legacyMatchesThis) {
                $nextAttachmentPath = PurchaseAttachment::where('purchase_id', $purchase->id)
                    ->orderByDesc('id')
                    ->value('file_path');

                $purchase->invoice_attachment = $nextAttachmentPath;
                $purchase->save();
            }

            DB::commit();
            Toastr::success('Attachment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Delete failed: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function downloadAttachment(string $id, string $attachmentId)
    {
        $purchase = Purchase::findOrFail($id);
        $attachment = PurchaseAttachment::where('purchase_id', $purchase->id)->findOrFail($attachmentId);
        $downloadName = $attachment->original_name ?: basename($attachment->file_path);
        $response = StoredFileSupport::download($attachment->file_path, $downloadName);

        if (!$response) {
            Toastr::error('Attachment file not found.');
            return redirect()->back();
        }

        return $response;
    }

    public function downloadLegacyAttachment(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        $downloadName = $purchase->invoice_attachment ? basename($purchase->invoice_attachment) : null;
        $response = StoredFileSupport::download($purchase->invoice_attachment, $downloadName);

        if (!$response) {
            Toastr::error('Attachment file not found.');
            return redirect()->back();
        }

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::with(['details', 'attachments'])->findOrFail($id);

            // Revert Stock
            foreach ($purchase->details as $detail) {
                // Decrement InventoryStock
                $variant_id = $detail->variant_id;
                
                $stock = \App\Models\InventoryStock::where('product_id', $detail->product_id)
                            ->where('variant_id', $variant_id)
                            ->where('outlet_id', 1)
                            ->first();

                if ($stock) {
                    $stock->decrement('quantity', $detail->qty);
                    
                    // Add Ledger Entry for Reversal
                    \App\Models\StockLedger::create([
                        'product_id' => $detail->product_id,
                        'variant_id' => $variant_id,
                        'outlet_id' => 1,
                        'reference_type' => 'purchase_delete',
                        'reference_id' => $purchase->id,
                        'in_qty' => 0,
                        'out_qty' => $detail->qty, // Out because we are reversing a purchase (in)
                        'balance_qty' => $stock->quantity,
                        'date' => date('Y-m-d') 
                    ]);
                }
            }

            $attachmentPaths = $purchase->attachments->pluck('file_path')->filter()->unique()->values()->all();
            if ($purchase->invoice_attachment) {
                $attachmentPaths[] = $purchase->invoice_attachment;
            }
            $attachmentPaths = array_values(array_unique($attachmentPaths));
            foreach ($attachmentPaths as $filePath) {
                StoredFileSupport::delete($filePath);
            }

            AuditLogSupport::log([
                'vendor_id' => $purchase->vendor_id,
                'module' => 'purchases',
                'action' => 'purchase_deleted',
                'entity_type' => 'purchase',
                'entity_id' => $purchase->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Purchase deleted and stock reverted.',
                'old_values' => [
                    'vendor_id' => $purchase->vendor_id,
                    'total_amount' => (float) $purchase->total_amount,
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'payment_status' => (string) $purchase->payment_status,
                    'detail_count' => $purchase->details->count(),
                    'attachment_count' => $purchase->attachments->count(),
                ],
            ]);

            $purchase->delete();

            DB::commit();

            return response(['status' => 'success', 'message' => 'Purchase Deleted and Stock Reverted Successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
}
