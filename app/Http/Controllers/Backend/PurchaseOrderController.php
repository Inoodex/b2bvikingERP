<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PoDataTable;
use App\Http\Controllers\Controller;
use App\Jobs\GeneratePoPdfJob;
use App\Models\ComparisonStatement;
use App\Models\ComparisonStatementItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Rfq;
use App\Support\PdfCacheManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(PoDataTable $dataTable)
    {
        return $dataTable->render('backend.purchase.po_list');
    }

    public function generateFromCs($csId): RedirectResponse
    {
        $cs = ComparisonStatement::with(['rfq', 'items.selectedQuotationItem.quotation'])->findOrFail($csId);

        if ($cs->approval_status !== 'approved') {
            Toastr::error('Comparison Statement must be Approved before generating Purchase Orders.');
            return redirect()->back();
        }

        try {
            DB::transaction(function () use ($cs) {
                // Group winning items by supplier
                $itemsByVendor = [];

                if ($cs->recommended_vendor_id) {
                    // Single vendor award for all items
                    $vendorId = $cs->recommended_vendor_id;
                    foreach ($cs->items as $csItem) {
                        $itemsByVendor[$vendorId][] = $csItem;
                    }
                } else {
                    // Split PO award based on item recommendations
                    foreach ($cs->items as $csItem) {
                        $vendorId = $csItem->recommended_vendor_id;
                        if ($vendorId) {
                            $itemsByVendor[$vendorId][] = $csItem;
                        }
                    }
                }

                if (empty($itemsByVendor)) {
                    throw new \Exception('No awarded vendors found in this Comparison Statement.');
                }

                foreach ($itemsByVendor as $vendorId => $csItems) {
                    // Generate sequential PO Number
                    $lastPo = Purchase::lockForUpdate()->latest('id')->first();
                    $nextId = $lastPo ? ($lastPo->id + 1) : 1;
                    $poNo = 'PO-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                    $firstItemQuotation = $csItems[0]->selectedQuotationItem->quotation ?? null;
                    $currencyId = $firstItemQuotation ? $firstItemQuotation->currency_id : null;
                    $exchangeRate = $firstItemQuotation && $firstItemQuotation->currency ? $firstItemQuotation->currency->exchange_rate : 1.0;

                    $totalForeignAmount = 0;

                    $purchase = Purchase::create([
                        'po_no' => $poNo,
                        'invoice_no' => $poNo,
                        'vendor_id' => $vendorId,
                        'user_id' => Auth::id(),
                        'date' => now(),
                        'purchase_type' => $currencyId ? 'foreign' : 'local',
                        'rfq_id' => $cs->rfq_id,
                        'comparison_statement_id' => $cs->id,
                        'currency_id' => $currencyId,
                        'exchange_rate_used' => $exchangeRate,
                        'approval_status' => 'approved',
                        'milestone_status' => 'approved',
                        'status' => '1',
                        'note' => 'Generated automatically from Comparison Statement ' . $cs->cs_no,
                    ]);

                    foreach ($csItems as $csItem) {
                        $unitPrice = $csItem->selectedQuotationItem ? $csItem->selectedQuotationItem->unit_price : 0;
                        $qty = $csItem->selectedQuotationItem ? $csItem->selectedQuotationItem->qty : null;
                        
                        if (!$qty || $qty <= 0) {
                            $rfqItem = \App\Models\RfqItem::where('rfq_id', $cs->rfq_id)
                                ->where('product_id', $csItem->product_id)
                                ->where('variant_id', $csItem->variant_id)
                                ->first();
                            $qty = $rfqItem ? $rfqItem->qty : 1;
                        }

                        $lineTotal = $unitPrice * $qty;
                        $totalForeignAmount += $lineTotal;

                        PurchaseDetail::create([
                            'purchase_id' => $purchase->id,
                            'product_id' => $csItem->product_id,
                            'variant_id' => $csItem->variant_id,
                            'qty' => $qty,
                            'unit_cost' => $unitPrice,
                            'total' => $lineTotal,
                        ]);
                    }

                    $baseAmount = $totalForeignAmount * $exchangeRate;
                    $purchase->update([
                        'foreign_amount' => $totalForeignAmount,
                        'total_amount' => $baseAmount,
                        'base_amount' => $baseAmount,
                    ]);

                    // Submit PO to Approval Workflow if needed
                    (new \App\Services\ApprovalService())->submitForApproval($purchase, (float)$baseAmount);
                }

                // Update RFQ Status to PO Issued (Closed)
                if ($cs->rfq) {
                    $cs->rfq->update(['status' => 'closed']);
                }
            });

            Toastr::success('Purchase Order(s) generated successfully!');
            return redirect()->route('admin.purchase-orders.index');
        } catch (\Exception $e) {
            Toastr::error('Failed to generate PO: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function show($id): View
    {
        $po = Purchase::with(['vendor', 'rfq', 'comparisonStatement', 'currency', 'items.product', 'items.variant', 'proformaInvoice', 'letterOfCredit.expenses', 'letterOfCredit.amendments', 'emailLogs'])->findOrFail($id);
        return view('backend.purchase.po_show', compact('po'));
    }

    public function cancel($id): RedirectResponse
    {
        $po = Purchase::findOrFail($id);
        $po->update([
            'milestone_status' => 'cancelled',
            'approval_status' => 'rejected',
        ]);
        Toastr::warning('Purchase Order ' . ($po->po_no ?? $po->id) . ' has been cancelled.');
        return redirect()->back();
    }

    public function sendEmail($id): RedirectResponse
    {
        $po = Purchase::with('vendor')->findOrFail($id);
        if (!$po->vendor || !$po->vendor->email) {
            Toastr::error('Vendor email address is missing.');
            return redirect()->back();
        }

        dispatch(function () use ($po) {
            try {
                \Illuminate\Support\Facades\Mail::to($po->vendor->email)->send(new \App\Mail\PoNotificationMail($po, $po->vendor));
                $po->emailLogs()->create([
                    'recipient_email' => $po->vendor->email,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $po->update(['milestone_status' => 'po_sent']);
            } catch (\Exception $e) {
                // Log failure
            }
        })->afterResponse();

        Toastr::success('PO Email has been sent to supplier background queue!');
        return redirect()->back();
    }

    /**
     * Stream (view inline) PO PDF in new tab.
     * If cache is fresh → serve from disk.
     * If cache is stale → generate synchronously so target=_blank new tab works correctly.
     * Also dispatches background job to warm cache for next request.
     */
    public function streamPdf(int $id)
    {
        $path = 'pos/po_' . $id . '.pdf';

        if (PdfCacheManager::isFresh($path, 3600)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="PO-' . $id . '.pdf"',
            ]);
        }

        // Cache stale — generate synchronously so the new-tab view returns a real PDF response
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $po = Purchase::with(['vendor', 'currency', 'items.product', 'items.variant'])->findOrFail($id);
        $settings = \App\Models\GeneralSetting::first();
        if ($settings && $settings->site_logo) {
            $settings->optimized_logo = \App\Support\PdfImageHelper::optimize($settings->site_logo, 480, 120, 95);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'sans-serif',
        ])->loadView('backend.purchase.po_pdf', compact('po', 'settings'));

        // Cache for future requests
        Storage::disk('public')->put($path, $pdf->output());

        // Also queue background job to refresh cache notification
        GeneratePoPdfJob::dispatch($id, Auth::id());

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="PO-' . $id . '.pdf"',
        ]);
    }

    /**
     * Download PO PDF — dispatches background job if cache is stale.
     * Matches Phase 1 RFQ downloadPdf() approach.
     */
    public function downloadPdf(int $id)
    {
        $path = 'pos/po_' . $id . '.pdf';

        if (!PdfCacheManager::isFresh($path, 3600)) {
            GeneratePoPdfJob::dispatch($id, Auth::id());
            Toastr::info('PO PDF is generating in the background. You will be notified once ready.');
            return redirect()->back();
        }

        return Storage::disk('public')->download($path, 'PO-' . $id . '.pdf');
    }
}
