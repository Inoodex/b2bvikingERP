<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\PurchaseReportService;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    protected PurchaseReportService $reportService;

    public function __construct(PurchaseReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Client Req 2.23: Supplier-wise Purchase Report
     */
    public function supplierWise(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id']);
        $reportData = $this->reportService->getSupplierWisePurchase($filters);
        $vendors = Vendor::where('status', 1)->get();

        return view('backend.purchase_report.supplier_wise', compact('reportData', 'vendors', 'filters'));
    }

    /**
     * Client Req 2.24 & 2.26: Item-wise Purchase Report
     */
    public function itemWise(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_id', 'product_id']);
        $reportData = $this->reportService->getItemWisePurchase($filters);
        $vendors = Vendor::where('status', 1)->get();
        $products = Product::where('status', 1)->get();

        return view('backend.purchase_report.item_wise', compact('reportData', 'vendors', 'products', 'filters'));
    }

    /**
     * Client Req 2.25: Total Purchase Value Periodic
     */
    public function totalValue(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $result = $this->reportService->getTotalPurchaseValue($filters);
        $summary = $result['summary'];
        $reportData = $result['reportData'];

        return view('backend.purchase_report.total_value', compact('summary', 'reportData', 'filters'));
    }

    /**
     * Client Req 2.27: Purchase Value vs Last Year Comparison
     */
    public function vsLastYear(Request $request)
    {
        $year = $request->query('year', now()->year);
        $comparison = $this->reportService->getPurchaseVsLastYear((int) $year);

        return view('backend.purchase_report.value_vs_last_year', compact('comparison', 'year'));
    }

    /**
     * Client Req 2.28 - 2.30: PR Received / Pending / Items Pending Reports
     */
    public function prStatus(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $prData = $this->reportService->getPrStatusReport($filters);

        return view('backend.purchase_report.pr_status', compact('prData', 'filters'));
    }

    /**
     * Client Req 2.31 - 2.32: Items Purchased & PO Issued List
     */
    public function poStatus(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $poList = $this->reportService->getPoIssuedReport($filters);

        return view('backend.purchase_report.po_status', compact('poList', 'filters'));
    }
}
