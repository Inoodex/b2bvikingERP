<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OrderDataTable;
use App\Http\Controllers\Controller;
use App\Mail\OrderPiInvoiceReadyMail;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\User;
use App\Support\PiInfoSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Support\PdfImageHelper;

class FrontendOrderController extends Controller
{
    /**
     * Display a listing of frontend orders.
     */
    public function index(OrderDataTable $dataTable)
    {
        $users = User::role(['Outlet User', 'User'])->where('status', 1)->orderBy('name')->get(['id', 'name', 'outlet_name']);
        return $dataTable->render('backend.orders.index', compact('users'));
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order)
    {
        $order->reconcileTotals();
        $order->refresh();
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'items.vendor', 'user', 'payments.receipts']);
        
        // Use issued items if any exist for this order, otherwise use original order items
        $items = $this->getIssuedItems($order);
        
        $piInfo = PiInfoSupport::prepare($order->pi_info, $items, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        // Calculate Credit Limit Exposure
        $creditService = app(\App\Services\Credit\CreditValidationService::class);
        $creditEvaluation = $creditService->evaluateCreditExposure($order->user_id, (float)$order->total_amount, $order->id);

        return view('backend.orders.show', compact('order', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'items', 'creditEvaluation'));
    }

    /**
     * View order invoice in browser (HTML).
     */
    public function viewInvoice(Order $order)
    {
        $order->reconcileTotals();
        $order->refresh();
        $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'user']);
        $settings = GeneralSetting::first();

        // Filter items to only include those that have been issued
        $issuedItems = $this->getIssuedItems($order);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $issuedItems, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        return view('backend.orders.invoice', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'issuedItems'));
    }

    /**
     * View order PI invoice in browser (HTML).
     */
    public function piInvoice(Order $order)
    {
        $order->reconcileTotals();
        $order->refresh();
        $order->load([
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
            'user'
        ]);
        $settings = GeneralSetting::first();

        // Filter items to only include those that have been issued
        $issuedItems = $this->getIssuedItems($order);
        $piInfo = PiInfoSupport::prepare($order->pi_info, $issuedItems, 'quantity');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($order->pi_info);

        $downloadUrl = route('admin.orders.pi-invoice.download', $order->id);

        return view('backend.orders.pi_invoice', compact('order', 'settings', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'downloadUrl', 'issuedItems'));
    }

    /**
     * Save manual PI/CTN information for an order.
     */
    public function savePiInfo(Request $request, Order $order)
    {
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

        $order->pi_info = PiInfoSupport::sanitizePayload($validated);
        $order->save();

        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        $this->notifyPiReady($order);

        Toastr::success('PI info saved successfully!');
        return redirect()->route('admin.orders.show', $order->id);
    }

    /**
     * Download order invoice as PDF.
     */
    public function downloadInvoice(Order $order)
    {
        $path = 'invoices/invoice-' . $order->order_no . '.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        // \App\Jobs\GeneratePdfJob::dispatch($order->id, 'invoice', \Illuminate\Support\Facades\Auth::id());
        
        // Toastr::info('PDF is generating in the background. Please refresh and click download again after a minute.');
        // return redirect()->back();

        \App\Jobs\GeneratePdfJob::dispatchSync($order->id, 'invoice', \Illuminate\Support\Facades\Auth::id());
        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    /**
     * Download order PI invoice as PDF.
     */
    public function downloadPiInvoice(Order $order)
    {
        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatch($order->id, 'pi_invoice', \Illuminate\Support\Facades\Auth::id());
        
        Toastr::info('PI Invoice is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();

        // \App\Jobs\GeneratePdfJob::dispatchSync($order->id, 'pi_invoice', \Illuminate\Support\Facades\Auth::id());
        // return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    /**
     * Download customer invoice as PDF.
     */
    public function downloadCustomerInvoice(Order $order)
    {
        $path = 'invoices/customer-invoice-' . $order->order_no . '.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GeneratePdfJob::dispatch($order->id, 'customer_invoice', \Illuminate\Support\Facades\Auth::id());
        
        Toastr::info('Customer Invoice is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();

        // \App\Jobs\GeneratePdfJob::dispatchSync($order->id, 'customer_invoice', \Illuminate\Support\Facades\Auth::id());
        // return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    /**
     * Update status for an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $order->status = $validated['status'];
        $order->save();

        Toastr::success('Order status updated successfully!');
        return redirect()->route('admin.orders.show', $order->id);
    }

    /**
     * Authorize Release for Credit Hold.
     */
    public function releaseCreditHold(Request $request, Order $order)
    {
        $request->validate([
            'override_reason' => 'required|string|max:255',
        ]);

        if ($order->status !== 'credit_hold') {
            Toastr::info('Order is not under Credit Hold.');
            return redirect()->back();
        }

        $order->update([
            'status' => 'approved',
            'pi_email' => 'Credit Override Granted: ' . $request->override_reason . ' (By ' . (auth()->user()?->name ?? 'Admin') . ' at ' . now()->format('d M Y H:i') . ')',
        ]);

        Toastr::success('Credit Hold released successfully for Order #' . $order->order_no . '!');
        return redirect()->route('admin.orders.show', $order->id);
    }

    /**
     * Remove an order from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully!',
        ]);
    }

    private function notifyPiReady(Order $order): void
    {
        $order->loadMissing('user');
        $recipient = $order->pi_email ?: $order->billing_email ?: ($order->user?->email ?? null);
        if (!$recipient) {
            return;
        }

        $attachPdf = (bool) config('mail.attach_pi_pdf', true);

        try {
            Mail::to($recipient)->send(new OrderPiInvoiceReadyMail(
                $order,
                route('orders.pi-invoice', $order->id),
                route('orders.pi-invoice.download', $order->id),
                $attachPdf
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to send PI invoice email for order.', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getIssuedItems(Order $order)
    {
        return $order->items;
    }
}
