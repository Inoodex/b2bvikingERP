<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\VendorBillDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\VendorBill\StoreVendorBillRequest;
use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Models\VendorBill;
use App\Models\VendorReturn;
use App\Services\VendorBillService;
use Illuminate\Http\Request;

class VendorBillController extends Controller
{
    protected VendorBillService $billService;

    public function __construct(VendorBillService $billService)
    {
        $this->billService = $billService;
    }

    /**
     * Display a listing of Vendor Bills.
     */
    public function index(VendorBillDataTable $dataTable)
    {
        return $dataTable->render('backend.vendor_bill.index');
    }

    /**
     * Show the form for creating a new Vendor Bill from a GRN or PO.
     */
    public function create(Request $request)
    {
        $grnId = $request->query('grn_id');
        $poId = $request->query('purchase_id');

        $goodsReceipt = null;
        $purchase = null;
        $pendingReturns = collect();
        $debitNoteAmount = 0;

        $purchases = Purchase::with(['vendor', 'currency'])->whereNotNull('po_no')->orWhere('approval_status', 'approved')->latest()->get();
        $goodsReceipts = GoodsReceipt::with(['purchase.vendor'])->latest()->get();

        if ($grnId) {
            $goodsReceipt = GoodsReceipt::with(['purchase.vendor', 'items.product', 'items.variant'])->find($grnId);
            if ($goodsReceipt) {
                $purchase = $goodsReceipt->purchase;
            }
        } else if ($poId) {
            $purchase = Purchase::with(['vendor', 'currency', 'details.product', 'details.variant'])->find($poId);
        }

        if ($purchase) {
            // Check for pending Debit Notes on this PO / Vendor Return
            $pendingReturns = VendorReturn::where('purchase_id', $purchase->id)
                ->where('status', 'approved')
                ->get();

            $debitNoteAmount = $pendingReturns->sum('total_claim_amount');
        }

        return view('backend.vendor_bill.create', compact('goodsReceipt', 'purchase', 'purchases', 'goodsReceipts', 'pendingReturns', 'debitNoteAmount'));
    }

    /**
     * Store a newly created Vendor Bill in storage.
     */
    public function store(StoreVendorBillRequest $request)
    {
        try {
            $bill = $this->billService->createBill($request->validated());

            toastr()->success('Vendor Bill ' . $bill->bill_no . ' created successfully!');
            return redirect()->route('admin.vendor-bills.show', $bill->id);
        } catch (\Exception $e) {
            toastr()->error('Failed to create Vendor Bill: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified Vendor Bill.
     */
    public function show(int $id)
    {
        $bill = VendorBill::with([
            'purchase',
            'vendor',
            'goodsReceipt',
            'currency',
            'createdBy',
            'items.product',
            'items.variant',
            'debitNoteSettlements.vendorReturn'
        ])->findOrFail($id);

        return view('backend.vendor_bill.show', compact('bill'));
    }
}
