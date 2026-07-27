<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\Currency;
use App\Services\Rfq\VendorQuotationService;
use App\Http\Requests\Backend\Rfq\StoreQuotationRequest;
use Brian2694\Toastr\Facades\Toastr;

class VendorQuotationController extends Controller
{
    protected VendorQuotationService $quotationService;

    public function __construct(VendorQuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    public function create(string $rfqId, string $vendorId)
    {
        $rfq = Rfq::with(['items.product', 'items.variant'])->findOrFail($rfqId);
        $vendor = Vendor::findOrFail($vendorId);
        $currencies = Currency::where('status', 1)->get();

        if ($rfq->status === 'closed') {
            Toastr::error('This RFQ is closed. You cannot submit new quotations.');
            return redirect()->route('admin.rfqs.show', $rfq->id);
        }

        return view('backend.rfq.quotation_form', compact('rfq', 'vendor', 'currencies'));
    }

    public function store(StoreQuotationRequest $request)
    {
        try {
            $this->quotationService->storeQuotation($request->validated());
            Toastr::success('Quotation Submitted Successfully!');
            return redirect()->route('admin.rfqs.show', $request->rfq_id);
        } catch (\Exception $e) {
            Toastr::error('Quotation Submission Failed: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
