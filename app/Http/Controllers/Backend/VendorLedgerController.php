<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\VendorLedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VendorLedgerController extends Controller
{
    protected VendorLedgerService $ledgerService;

    public function __construct(VendorLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display AP Summary Dashboard for all vendors.
     */
    public function index()
    {
        $vendors = Vendor::where('status', 1)->get();
        $agingSummary = $this->ledgerService->getAgingReport();

        return view('backend.vendor_ledger.index', compact('vendors', 'agingSummary'));
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

        return view('backend.vendor_ledger.show', compact('statement', 'fromDate', 'toDate'));
    }

    /**
     * Display AP Aging Report across all vendors.
     */
    public function agingReport()
    {
        $agingData = $this->ledgerService->getAgingReport();

        return view('backend.vendor_ledger.aging', compact('agingData'));
    }

    /**
     * Export Supplier Outstanding Confirmation Letter PDF.
     */
    public function exportPdf(int $vendorId, Request $request)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $statement = $this->ledgerService->getVendorStatement($vendor, $fromDate, $toDate);

        $pdf = Pdf::loadView('backend.vendor_ledger.statement_pdf', compact('statement'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Supplier_Statement_' . str_replace(' ', '_', $vendor->name) . '.pdf');
    }
}
