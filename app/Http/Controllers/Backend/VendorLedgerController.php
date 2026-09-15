<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\VendorLedgerDataTable;
use App\Http\Controllers\Controller;
use App\Jobs\GeneratePayablesReceivablesReportPdfJob;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\VendorLedgerService;
use App\Traits\HasEphemeralPdfReports;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorLedgerController extends Controller
{
    use HasEphemeralPdfReports;

    protected VendorLedgerService $ledgerService;

    public function __construct(VendorLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display AP Summary Dashboard & Yajra DataTable for all vendors.
     */
    public function index(VendorLedgerDataTable $dataTable)
    {
        $totalVendors = Vendor::where('status', 1)->count();
        $vendorsWithDue = VendorBill::whereIn('payment_status', ['unpaid', 'partial'])
            ->where('due_amount', '>', 0)
            ->distinct('vendor_id')
            ->count('vendor_id');

        $summary = [
            'total_payables'  => (float) VendorBill::whereIn('payment_status', ['unpaid', 'partial'])->sum('due_amount'),
            'total_vendors'   => $totalVendors,
            'active_dues'     => $vendorsWithDue,
            'settled_vendors' => max(0, $totalVendors - $vendorsWithDue),
        ];

        return $dataTable->render('backend.vendor_ledger.index', compact('summary'));
    }

    /**
     * Display running Statement of Account for a specific vendor.
     */
    public function show(int $vendorId, Request $request)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $statement = $this->ledgerService->getVendorStatement($vendor, $fromDate, $toDate);
        $latestPdf = $this->getLatestReportFile("supplier_statement_{$vendorId}");

        return view('backend.vendor_ledger.show', compact('statement', 'fromDate', 'toDate', 'latestPdf'));
    }

    /**
     * Display AP Aging Report across all vendors.
     */
    public function agingReport()
    {
        $agingData = $this->ledgerService->getAgingReport();
        $latestPdf = $this->getLatestReportFile('ap_aging');

        return view('backend.vendor_ledger.aging', compact('agingData', 'latestPdf'));
    }

    /**
     * Export Supplier Outstanding Confirmation Letter PDF via background queue.
     */
    public function exportPdf(int $vendorId, Request $request): JsonResponse|RedirectResponse
    {
        $vendor = Vendor::findOrFail($vendorId);
        $filters = [
            'vendor_id' => $vendorId,
            'from_date' => $request->query('from_date'),
            'to_date'   => $request->query('to_date'),
        ];

        dispatch(new GeneratePayablesReceivablesReportPdfJob('supplier_statement', $filters, (int) auth()->id()));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'        => 'dispatched',
                'message'       => 'Supplier statement PDF generation started in the background.',
                'dispatched_at' => time() - 1,
            ]);
        }

        Toastr::info('Supplier statement PDF is generating in the background. Check notifications when ready.');
        return redirect()->back();
    }

    /**
     * Export AP Vendor Aging PDF via background queue.
     */
    public function exportAgingPdf(Request $request): JsonResponse|RedirectResponse
    {
        $filters = [];

        dispatch(new GeneratePayablesReceivablesReportPdfJob('ap_aging', $filters, (int) auth()->id()));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'        => 'dispatched',
                'message'       => 'AP Vendor Aging PDF generation started in the background.',
                'dispatched_at' => time() - 1,
            ]);
        }

        Toastr::info('AP Vendor Aging PDF is generating in the background. Check notifications when ready.');
        return redirect()->back();
    }
}
