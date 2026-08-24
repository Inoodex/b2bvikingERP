<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\VendorQuotation;
use App\Models\ComparisonStatement;
use App\Models\ComparisonStatementItem;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class ComparisonStatementController extends Controller
{
    public function create($rfqId)
    {
        $rfq = Rfq::with(['items.product', 'items.variant'])->findOrFail($rfqId);
        
        $hasPos = \App\Models\Purchase::whereHas('comparisonStatement', fn($q) => $q->where('rfq_id', $rfqId))->exists();
        if ($hasPos) {
            Toastr::warning('Comparison Statement evaluation is locked because Purchase Order(s) have already been issued.', 'Locked');
            return redirect()->route('admin.rfqs.show', $rfqId);
        }

        // Fetch all received quotations for this RFQ
        $quotations = VendorQuotation::with(['vendor', 'currency', 'items'])
            ->where('rfq_id', $rfqId)
            ->get();
            
        if ($quotations->isEmpty()) {
            Toastr::warning('No quotations received for this RFQ yet.');
            return redirect()->back();
        }

        // Calculate L1 (Lowest Bidder) logic here if needed, or do it in Blade
        return view('backend.rfq.cs_matrix', compact('rfq', 'quotations'));
    }

    public function store(Request $request, $rfqId)
    {
        $request->validate([
            'award_type' => 'required|in:single,split',
            'recommended_vendor_id' => 'required_if:award_type,single|nullable|exists:vendors,id',
            'items' => 'required_if:award_type,split|array',
            'items.*.selected_vqi_id' => 'nullable|exists:vendor_quotation_items,id',
        ]);

        $hasPos = \App\Models\Purchase::whereHas('comparisonStatement', fn($q) => $q->where('rfq_id', $rfqId))->exists();
        if ($hasPos) {
            Toastr::error('Cannot re-evaluate or modify Comparison Statement because Purchase Order(s) have already been generated.', 'Action Locked');
            return redirect()->route('admin.rfqs.show', $rfqId);
        }

        try {
            DB::beginTransaction();

            // Delete previous CS records for this RFQ if regenerating
            $existingCsList = ComparisonStatement::where('rfq_id', $rfqId)->get();
            foreach ($existingCsList as $oldCs) {
                $oldCs->items()->delete();
                $oldCs->approvals()->delete();
                \App\Support\PdfCacheManager::clearCsCache($oldCs->id);
                $oldCs->delete();
            }

            // Generate Sequential CS Number (CS-00001, CS-00002, etc.)
            $lastCs = ComparisonStatement::latest('id')->first();
            $nextId = $lastCs ? ($lastCs->id + 1) : 1;
            $csNo = 'CS-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Create CS
            $cs = ComparisonStatement::create([
                'cs_no' => $csNo,
                'rfq_id' => $rfqId,
                'recommended_vendor_id' => $request->award_type === 'single' ? $request->recommended_vendor_id : null,
                'approval_status' => 'draft'
            ]);

            $rfq = Rfq::with('items')->findOrFail($rfqId);

            // If single vendor award, fetch the recommended vendor's quotation
            $singleVendorQuotation = null;
            if ($request->award_type === 'single') {
                $singleVendorQuotation = VendorQuotation::with('items')
                    ->where('rfq_id', $rfqId)
                    ->where('vendor_id', $request->recommended_vendor_id)
                    ->first();
            }

            foreach ($rfq->items as $index => $rfqItem) {
                $selectedVqiId = null;
                
                if ($request->award_type === 'split' && isset($request->items[$index]['selected_vqi_id'])) {
                    $selectedVqiId = $request->items[$index]['selected_vqi_id'];
                } elseif ($request->award_type === 'single' && $singleVendorQuotation) {
                    $matchingVqi = $singleVendorQuotation->items
                        ->where('product_id', $rfqItem->product_id)
                        ->where('variant_id', $rfqItem->variant_id)
                        ->first() ?? $singleVendorQuotation->items
                        ->where('product_id', $rfqItem->product_id)
                        ->first();
                    if ($matchingVqi) {
                        $selectedVqiId = $matchingVqi->id;
                    }
                }

                ComparisonStatementItem::create([
                    'comparison_statement_id' => $cs->id,
                    'product_id' => $rfqItem->product_id,
                    'variant_id' => $rfqItem->variant_id,
                    'selected_vendor_quotation_item_id' => $selectedVqiId,
                ]);
            }

            // Reload relationships to compute exact total amount
            $cs->load('items.selectedQuotationItem');
            $totalAmount = $cs->total_amount;
            (new \App\Services\ApprovalService())->submitForApproval($cs, (float)$totalAmount);

            // Dispatch background PDF generation job immediately
            \App\Jobs\GenerateCsPdfJob::dispatch($cs->id, \Illuminate\Support\Facades\Auth::id());

            DB::commit();
            Toastr::success('Comparison Statement Generated Successfully!');
            return redirect()->route('admin.rfqs.show', $rfqId);

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error generating CS: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function approve(Request $request, ComparisonStatement $cs)
    {
        $success = (new \App\Services\ApprovalService())->approveStep($cs, \Illuminate\Support\Facades\Auth::id());
        if ($success) {
            \App\Support\PdfCacheManager::clearCsCache($cs->id);
            Toastr::success('CS Approval Step Approved Successfully!');
        } else {
            Toastr::error('Failed to approve CS step.');
        }
        return redirect()->back();
    }

    public function reject(Request $request, ComparisonStatement $cs)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $success = (new \App\Services\ApprovalService())->rejectStep($cs, \Illuminate\Support\Facades\Auth::id(), $request->reason);
        if ($success) {
            \App\Support\PdfCacheManager::clearCsCache($cs->id);
            Toastr::success('CS Rejected Successfully!');
        } else {
            Toastr::error('Failed to reject CS.');
        }
        return redirect()->back();
    }

    public function downloadPdf($rfqId, $csId)
    {
        $path = 'cs/cs_' . $csId . '.pdf';
        if (!\App\Support\PdfCacheManager::isFresh($path, 3600)) {
            \App\Jobs\GenerateCsPdfJob::dispatch($csId, \Illuminate\Support\Facades\Auth::id());
            Toastr::info('CS PDF is generating in the background. You will be notified once ready.');
            return redirect()->back();
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->download($path, 'CS-' . $csId . '.pdf');
    }

    public function streamPdf($rfqId, $csId)
    {
        $path = 'cs/cs_' . $csId . '.pdf';
        if (!\App\Support\PdfCacheManager::isFresh($path, 3600)) {
            \App\Jobs\GenerateCsPdfJob::dispatch($csId, \Illuminate\Support\Facades\Auth::id());
            Toastr::info('CS PDF is generating in the background. You will be notified once ready.');
            return redirect()->back();
        }
        return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="CS-' . $csId . '.pdf"'
        ]);
    }
}
