<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockTransferDataTable;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use App\Services\Inventory\StockTransferService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    protected StockTransferService $transferService;

    public function __construct(StockTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(StockTransferDataTable $dataTable)
    {
        return $dataTable->render('backend.stock_transfers.index');
    }

    public function create()
    {
        $outlets = Outlet::all();
        if ($outlets->isEmpty()) {
            $outlets = User::role(['Outlet User', 'User'])->get();
        }

        $products = Product::where('status', 1)
            ->with(['variants.color', 'variants.size', 'unit'])
            ->orderBy('name')
            ->get();

        return view('backend.stock_transfers.create', compact('outlets', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_outlet_id' => 'required',
            'to_outlet_id' => 'required|different:from_outlet_id',
            'transfer_date' => 'required|date',
            'challan_no' => 'nullable|string|max:100',
            'vehicle_no' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:150',
            'driver_phone' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.item_note' => 'nullable|string|max:255',
        ]);

        try {
            $transfer = $this->transferService->createTransfer($validated, $request->items);
            Toastr::success('Stock Transfer created successfully in Draft state.');
            return redirect()->route('admin.stock-transfers.show', $transfer->id);
        } catch (\Exception $e) {
            Toastr::error('Failed to create Stock Transfer: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'items.product.unit',
            'items.variant.color',
            'items.variant.size',
            'fromOutlet',
            'toOutlet',
            'requestedByUser',
            'dispatchedByUser',
            'receivedByUser'
        ]);

        return view('backend.stock_transfers.show', compact('stockTransfer'));
    }

    public function dispatchTransfer(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->dispatchTransfer($stockTransfer);
            Toastr::success('Stock Transfer dispatched! Stock has been deducted from source warehouse and is now in transit.');
        } catch (\Exception $e) {
            Toastr::error('Dispatch Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
    }

    public function receiveForm(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'dispatched') {
            Toastr::warning('Only dispatched transfers in transit can be received.');
            return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
        }

        $stockTransfer->load(['items.product.unit', 'items.variant.color', 'items.variant.size', 'fromOutlet', 'toOutlet']);
        return view('backend.stock_transfers.receive', compact('stockTransfer'));
    }

    public function receiveTransfer(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'received_items' => 'required|array',
            'received_items.*' => 'required|numeric|min:0',
        ]);

        try {
            $this->transferService->receiveTransfer($stockTransfer, $request->received_items);
            Toastr::success('Stock Transfer received and verified! Stock has been added to destination warehouse.');
        } catch (\Exception $e) {
            Toastr::error('Receive Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
    }

    public function cancel(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->cancelTransfer($stockTransfer);
            Toastr::info('Stock Transfer has been cancelled.');
        } catch (\Exception $e) {
            Toastr::error('Cancellation Failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.stock-transfers.show', $stockTransfer->id);
    }

    public function downloadPdf(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'items.product.unit',
            'items.variant.color',
            'items.variant.size',
            'fromOutlet',
            'toOutlet',
            'requestedByUser',
            'dispatchedByUser'
        ]);

        $settings = GeneralSetting::first();

        $pdf = Pdf::loadView('backend.stock_transfers.pdf', compact('stockTransfer', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Transfer_Challan_' . $stockTransfer->transfer_no . '.pdf');
    }
}
