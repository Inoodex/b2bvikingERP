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

        try {
            DB::beginTransaction();

            // Create CS
            $cs = ComparisonStatement::create([
                'cs_no' => 'CS-' . time(), // simplistic numbering for now
                'rfq_id' => $rfqId,
                'recommended_vendor_id' => $request->award_type === 'single' ? $request->recommended_vendor_id : null,
                'approval_status' => 'draft'
            ]);

            $rfq = Rfq::with('items')->findOrFail($rfqId);

            foreach ($rfq->items as $index => $rfqItem) {
                $selectedVqiId = null;
                
                if ($request->award_type === 'split' && isset($request->items[$index]['selected_vqi_id'])) {
                    $selectedVqiId = $request->items[$index]['selected_vqi_id'];
                }

                ComparisonStatementItem::create([
                    'comparison_statement_id' => $cs->id,
                    'product_id' => $rfqItem->product_id,
                    'variant_id' => $rfqItem->variant_id,
                    'selected_vendor_quotation_item_id' => $selectedVqiId,
                ]);
            }

            // Submit for Approval automatically
            (new \App\Services\ApprovalService())->submitForApproval($cs);

            DB::commit();
            Toastr::success('Comparison Statement Generated Successfully!');
            return redirect()->route('admin.rfqs.show', $rfqId);

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error generating CS: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
