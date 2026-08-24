<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\Order;
use App\Models\CustomProductRequest;
use App\Models\ProductRequest;
use App\Models\Vendor;
use App\Models\Product;
use App\Services\Rfq\RfqService;
use App\Http\Requests\Backend\Rfq\StoreRfqRequest;
use App\Http\Requests\Backend\Rfq\UpdateRfqRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class RfqController extends Controller
{
    protected RfqService $rfqService;

    public function __construct(RfqService $rfqService)
    {
        $this->rfqService = $rfqService;
    }

    public function index(\App\DataTables\RfqDataTable $dataTable)
    {
        return $dataTable->render('backend.rfq.index');
    }

    public function create(Request $request): \Illuminate\Contracts\View\View
    {
        // Fetch various procurement triggers
        $orders = Order::whereNotIn('status', ['completed', 'cancelled'])->latest()->get();
        $customRequests = CustomProductRequest::where('status', 'approved')->latest()->get();
        $productRequests = ProductRequest::where('status', 'approved')->latest()->get();
        
        $vendors = Vendor::where('status', 1)->get();
        $products = Product::where('status', 1)->with('variants')->get();
        
        $selectedSourceType = $request->query('source_type');
        $selectedSourceId = $request->query('source_id');

        $latestRfq = Rfq::latest('id')->first();
        $nextId = $latestRfq ? $latestRfq->id + 1 : 1;
        $rfqNo = 'RFQ-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('backend.rfq.create', compact('orders', 'customRequests', 'productRequests', 'vendors', 'products', 'selectedSourceType', 'selectedSourceId', 'rfqNo'));
    }

    public function store(StoreRfqRequest $request)
    {
        try {
            $this->rfqService->storeRfq($request->validated());
            Toastr::success('RFQ Created Successfully!');
            return redirect()->route('admin.rfqs.index');
        } catch (\Exception $e) {
            Toastr::error('Something went wrong: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(string $id)
    {
        $rfq = Rfq::with(['items.product', 'items.variant', 'vendors.vendor', 'source', 'quotations.items', 'quotations.currency'])->findOrFail($id);
        $cs = \App\Models\ComparisonStatement::with([
            'items.product',
            'items.selectedQuotationItem.quotation.vendor',
            'recommendedVendor',
            'approvals.step.approverRole',
            'approvals.user',
            'purchases.vendor'
        ])
            ->where('rfq_id', $id)
            ->latest()
            ->first();

        return view('backend.rfq.show', compact('rfq', 'cs'));
    }

    public function edit(string $id)
    {
        $rfq = Rfq::with(['items', 'vendors'])->findOrFail($id);
        $productRequests = ProductRequest::where('status', 'approved')->get();
        $vendors = Vendor::where('status', 1)->get();
        $products = Product::where('status', 1)->with('variants')->get();
        
        return view('backend.rfq.edit', compact('rfq', 'productRequests', 'vendors', 'products'));
    }

    public function update(UpdateRfqRequest $request, string $id)
    {
        try {
            $rfq = Rfq::findOrFail($id);
            $this->rfqService->updateRfq($rfq, $request->validated());
            Toastr::success('RFQ Updated Successfully!');
            return redirect()->route('admin.rfqs.index');
        } catch (\Exception $e) {
            Toastr::error('Something went wrong: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $rfq = Rfq::findOrFail($id);
            $rfq->delete();
            return response(['status' => 'success', 'message' => 'RFQ Deleted Successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function close(string $id)
    {
        try {
            $this->rfqService->closeRfq($id);
            Toastr::success('RFQ Closed Successfully!');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function fetchSourceItems(Request $request)
    {
        $sourceType = $request->query('source_type');
        $sourceId = $request->query('source_id');

        if ($sourceType === 'App\Models\Order' && $sourceId) {
            $order = Order::with('items.product')->find($sourceId);
            if ($order) {
                $items = $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => optional($item->product)->name ?? 'Unknown Product',
                        'qty' => $item->quantity,
                    ];
                });
                return response()->json(['status' => 'success', 'items' => $items]);
            }
        }

        return response()->json(['status' => 'success', 'items' => []]);
    }

    public function sendVendorEmails(string $rfqId)
    {
        try {
            $rfq = Rfq::with(['items.product', 'vendors'])->findOrFail($rfqId);

            if ($rfq->vendors->isEmpty()) {
                Toastr::warning('No vendors invited to this RFQ.');
                return redirect()->back();
            }

            foreach ($rfq->vendors as $rv) {
                \App\Jobs\SendRfqEmailJob::dispatch($rfq, $rv->vendor_id);
            }

            Toastr::success('RFQ Emails queued successfully for background delivery!');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Error queuing RFQ emails: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function streamPdf(string $id)
    {
        $path = 'rfqs/rfq_' . $id . '.pdf';
        if (!\App\Support\PdfCacheManager::isFresh($path, 3600)) {
            \App\Jobs\GenerateRfqPdfJob::dispatch($id, \Illuminate\Support\Facades\Auth::id());
            Toastr::info('RFQ PDF is generating in the background. You will be notified once ready.');
            return redirect()->back();
        }
        return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="RFQ-' . $id . '.pdf"'
        ]);
    }

    public function downloadPdf(string $id)
    {
        $path = 'rfqs/rfq_' . $id . '.pdf';
        if (!\App\Support\PdfCacheManager::isFresh($path, 3600)) {
            \App\Jobs\GenerateRfqPdfJob::dispatch($id, \Illuminate\Support\Facades\Auth::id());
            Toastr::info('RFQ PDF is generating in the background. You will be notified once ready.');
            return redirect()->back();
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->download($path, 'RFQ-' . $id . '.pdf');
    }
}
