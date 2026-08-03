<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PurchasePaymentDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\PurchasePayment\StorePurchasePaymentRequest;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\VendorPaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchasePaymentController extends Controller
{
    protected VendorPaymentService $paymentService;

    public function __construct(VendorPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of Payment Vouchers.
     */
    public function index(PurchasePaymentDataTable $dataTable)
    {
        return $dataTable->render('backend.purchase_payment.index');
    }

    /**
     * Show the form for creating a new Payment Voucher.
     */
    public function create(Request $request)
    {
        $billId = $request->query('bill_id');
        $poId = $request->query('purchase_id');

        $vendorBill = null;
        $purchase = null;

        if ($billId) {
            $vendorBill = VendorBill::with(['purchase', 'vendor', 'currency'])->findOrFail($billId);
            $purchase = $vendorBill->purchase;
        } else if ($poId) {
            $purchase = Purchase::with(['vendor', 'currency'])->findOrFail($poId);
        }

        $purchases = Purchase::where('status', 1)->whereIn('payment_status', ['unpaid', 'partial'])->get();
        $vendors = Vendor::where('status', 1)->get();
        $currencies = Currency::all();

        return view('backend.purchase_payment.create', compact('vendorBill', 'purchase', 'purchases', 'vendors', 'currencies'));
    }

    /**
     * Store a newly created Payment Voucher in storage.
     */
    public function store(StorePurchasePaymentRequest $request)
    {
        try {
            $receiptFile = $request->file('receipt');
            $payment = $this->paymentService->processPayment($request->validated(), $receiptFile);

            toastr()->success('Payment Voucher ' . $payment->payment_no . ' recorded successfully!');
            return redirect()->route('admin.purchase-payments.show', $payment->id);
        } catch (\Exception $e) {
            toastr()->error('Failed to record payment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified Payment Voucher.
     */
    public function show(int $id)
    {
        $payment = PurchasePayment::with([
            'purchase',
            'vendor',
            'currency',
            'createdBy',
            'receipts'
        ])->findOrFail($id);

        return view('backend.purchase_payment.show', compact('payment'));
    }

    /**
     * Stream official Payment Voucher Slip PDF.
     */
    public function streamPdf(int $id)
    {
        $payment = PurchasePayment::with([
            'purchase',
            'vendor',
            'currency',
            'createdBy'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('backend.purchase_payment.pdf', compact('payment'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Payment_Voucher_' . $payment->payment_no . '.pdf');
    }
}
