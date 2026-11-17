<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\DocumentSequence;
use App\Models\GeneralSetting;
use App\Models\StationeryCategory;
use App\Models\StationeryConsumption;
use App\Models\StationeryItem;
use App\Models\StationeryPurchase;
use App\Models\StationeryStockLedger;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\StationeryInventoryService;
use App\Traits\ImageUploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StationeryController extends Controller
{
    use ImageUploadTrait;

    protected StationeryInventoryService $service;

    public function __construct(StationeryInventoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Stationery Items Master List & KPI Dashboard
     */
    public function index(Request $request)
    {
        $query = StationeryItem::with(['stationeryCategory', 'unit']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('stationery_category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'low_stock') {
                $query->whereColumn('current_stock', '<=', 'min_alert_qty');
            } elseif ($request->status === 'out_of_stock') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->status === 'healthy') {
                $query->whereColumn('current_stock', '>', 'min_alert_qty');
            }
        }

        $items = $query->orderBy('name')->paginate(25)->withQueryString();
        $kpis = $this->service->getDashboardKpis();
        $categories = StationeryCategory::where('status', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $settings = GeneralSetting::first();

        return view('backend.stationery.index', compact(
            'items', 'kpis', 'units', 'settings', 'categories'
        ));
    }

    /**
     * Store new Stationery Item
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stationery_category_id' => 'required|exists:stationery_categories,id',
            'unit_name' => 'nullable|string|max:50',
            'unit_id' => 'nullable|exists:units,id',
            'min_alert_qty' => 'required|numeric|min:0',
            'initial_stock' => 'nullable|numeric|min:0',
            'initial_cost' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:3072',
            'notes' => 'nullable|string',
        ]);

        $code = 'ST-' . strtoupper(Str::random(3)) . '-' . rand(100, 999);

        // Fetch category details
        $cat = StationeryCategory::findOrFail($request->stationery_category_id);

        // Fetch unit name if selected
        $unitName = $request->unit_name ?: 'Pcs';
        if ($request->unit_id) {
            $unit = Unit::find($request->unit_id);
            if ($unit) {
                $unitName = $unit->name;
            }
        }

        // Upload optional image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->upload_image($request, 'image', 'uploads/stationery');
        }

        $initialStock = (float) ($request->initial_stock ?? 0);
        $initialCost = (float) ($request->initial_cost ?? 0);

        $item = StationeryItem::create([
            'item_code' => $code,
            'name' => $request->name,
            'image' => $imagePath,
            'category' => $cat->slug,
            'stationery_category_id' => $cat->id,
            'unit_id' => $request->unit_id,
            'unit_name' => $unitName,
            'current_stock' => $initialStock,
            'unit_cost' => $initialCost,
            'min_alert_qty' => (float) $request->min_alert_qty,
            'allow_sale' => false,
            'status' => true,
            'notes' => $request->notes,
        ]);

        // If initial stock was provided, create an opening balance ledger entry
        if ($initialStock > 0) {
            StationeryStockLedger::create([
                'stationery_item_id' => $item->id,
                'transaction_type' => 'adjustment_in',
                'reference_id' => null,
                'reference_no' => 'OPENING-STOCK',
                'date' => Carbon::now()->toDateString(),
                'stock_before' => 0.00,
                'qty_in' => $initialStock,
                'qty_out' => 0.00,
                'stock_after' => $initialStock,
                'unit_cost' => $initialCost,
                'total_value' => round($initialStock * $initialCost, 2),
                'created_by' => Auth::id(),
                'notes' => 'Initial opening balance stock',
            ]);
        }

        toastr()->success('Stationery item created successfully!');
        return redirect()->route('admin.stationery-supplies.index');
    }

    /**
     * Update Stationery Item
     */
    public function updateItem(Request $request, StationeryItem $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stationery_category_id' => 'required|exists:stationery_categories,id',
            'unit_name' => 'nullable|string|max:50',
            'unit_id' => 'nullable|exists:units,id',
            'min_alert_qty' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:3072',
            'notes' => 'nullable|string',
        ]);

        $cat = StationeryCategory::findOrFail($request->stationery_category_id);

        $unitName = $request->unit_name ?: $item->unit_name;
        if ($request->unit_id) {
            $unit = Unit::find($request->unit_id);
            if ($unit) {
                $unitName = $unit->name;
            }
        }

        $imagePath = $item->image;
        if ($request->hasFile('image')) {
            $imagePath = $this->update_image($request, 'image', 'uploads/stationery', $item->image);
        }

        $item->update([
            'name' => $request->name,
            'image' => $imagePath,
            'category' => $cat->slug,
            'stationery_category_id' => $cat->id,
            'unit_id' => $request->unit_id,
            'unit_name' => $unitName,
            'min_alert_qty' => (float) $request->min_alert_qty,
            'notes' => $request->notes,
        ]);

        toastr()->success('Stationery item updated successfully!');
        return redirect()->route('admin.stationery-supplies.index');
    }

    /**
     * Delete Stationery Item
     */
    public function destroyItem(StationeryItem $item)
    {
        if ($item->image) {
            $this->delete_image($item->image);
        }

        $item->delete();
        toastr()->success('Stationery item archived successfully!');
        return redirect()->route('admin.stationery-supplies.index');
    }

    /**
     * Purchases List (Stock Inward History)
     */
    public function purchases(Request $request)
    {
        $query = StationeryPurchase::with(['vendor', 'creator', 'items.stationeryItem']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhere('invoice_no', 'like', "%{$search}%")
                  ->orWhere('vendor_name_custom', 'like', "%{$search}%");
            });
        }

        $purchases = $query->orderByDesc('purchase_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $settings = GeneralSetting::first();

        $paymentAccounts = ChartOfAccount::where('is_group', false)
            ->where(function ($q) {
                $q->whereIn('account_code', ['1010', '1020', '1021', '1022', '1023'])
                  ->orWhere(function ($sub) {
                      $sub->where('account_type', 'asset')->where('parent_id', 1);
                  });
            })
            ->orderBy('account_code')
            ->get();

        return view('backend.stationery.purchases.index', compact('purchases', 'settings', 'paymentAccounts'));
    }

    /**
     * Create Purchase Form
     */
    public function createPurchase()
    {
        $items = StationeryItem::where('status', true)->orderBy('name')->get();
        $settings = GeneralSetting::first();
        $nextPurchaseNo = DocumentSequence::previewNext('StationeryPurchase');

        $paymentAccounts = ChartOfAccount::where('is_group', false)
            ->where(function ($q) {
                $q->whereIn('account_code', ['1010', '1020', '1021', '1022', '1023'])
                  ->orWhere(function ($sub) {
                      $sub->where('account_type', 'asset')->where('parent_id', 1);
                  });
            })
            ->orderBy('account_code')
            ->get();

        $recentShops = StationeryPurchase::whereNotNull('vendor_name_custom')
            ->where('vendor_name_custom', '!=', '')
            ->distinct()
            ->pluck('vendor_name_custom');

        return view('backend.stationery.purchases.create', compact('items', 'settings', 'nextPurchaseNo', 'paymentAccounts', 'recentShops'));
    }

    /**
     * Store Purchase & Process Stock Inward
     */
    public function storePurchase(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'vendor_name_custom' => 'nullable|string|max:190',
            'invoice_no' => 'nullable|string|max:100',
            'payment_status' => 'required|in:paid,unpaid',
            'payment_account_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.stationery_item_id' => 'required|exists:stationery_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Generate sequential PO number using core ERP DocumentSequence engine
        $purchaseNo = DocumentSequence::generateNext('StationeryPurchase');

        $paymentStatus = $request->payment_status ?: 'paid';
        $paymentAccountCode = ($paymentStatus === 'paid')
            ? ($request->payment_account_code ?: '1010')
            : '2010';

        $purchase = StationeryPurchase::create([
            'purchase_no' => $purchaseNo,
            'vendor_id' => null,
            'vendor_name_custom' => $request->vendor_name_custom ?: 'Direct / Local Cash Market',
            'purchase_date' => $request->purchase_date,
            'invoice_no' => $request->invoice_no,
            'payment_status' => $paymentStatus,
            'payment_account_code' => $paymentAccountCode,
            'paid_amount' => ($paymentStatus === 'paid') ? 0.00 : 0.00, // Will be set to total_amount in recordPurchaseInward if paid
            'created_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        $this->service->recordPurchaseInward($purchase, $request->items);

        toastr()->success("Purchase {$purchase->purchase_no} recorded and stock updated with Weighted Average Cost!");
        return redirect()->route('admin.stationery-supplies.purchases');
    }

    /**
     * Settle Due/Credit Payment for an unpaid stationery purchase
     */
    public function settlePurchasePayment(Request $request, StationeryPurchase $purchase)
    {
        $request->validate([
            'payment_account_code' => 'required|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->settleDuePayment(
                $purchase,
                $request->payment_account_code,
                $request->payment_date,
                $request->notes
            );
            toastr()->success("Payment for {$purchase->purchase_no} settled successfully!");
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Consumptions List (Department Issue Vouchers)
     */
    public function consumptions(Request $request)
    {
        $query = StationeryConsumption::with(['department', 'creator', 'items.stationeryItem']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('issue_no', 'like', "%{$search}%")
                  ->orWhere('issued_to', 'like', "%{$search}%")
                  ->orWhere('department_name', 'like', "%{$search}%");
            });
        }

        $consumptions = $query->orderByDesc('issue_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $settings = GeneralSetting::first();

        return view('backend.stationery.consumptions.index', compact('consumptions', 'settings'));
    }

    /**
     * Create Department Consumption Issue Form
     */
    public function createConsumption()
    {
        $departments = Department::orderBy('name')->get();
        $items = StationeryItem::where('status', true)->where('current_stock', '>', 0)->orderBy('name')->get();
        $settings = GeneralSetting::first();
        $nextIssueNo = DocumentSequence::previewNext('StationeryConsumption');

        return view('backend.stationery.consumptions.create', compact('departments', 'items', 'settings', 'nextIssueNo'));
    }

    /**
     * Store Consumption Issue & Deduct Stock
     */
    public function storeConsumption(Request $request)
    {
        $request->validate([
            'issue_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'department_name' => 'nullable|string|max:120',
            'issued_to' => 'nullable|string|max:150',
            'items' => 'required|array|min:1',
            'items.*.stationery_item_id' => 'required|exists:stationery_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $departmentName = $request->department_name;
        if ($request->department_id) {
            $dept = Department::find($request->department_id);
            if ($dept) {
                $departmentName = $dept->name;
            }
        }

        // Generate sequential Issue Voucher number using core ERP DocumentSequence engine
        $issueNo = DocumentSequence::generateNext('StationeryConsumption');

        $consumption = StationeryConsumption::create([
            'issue_no' => $issueNo,
            'department_id' => $request->department_id,
            'department_name' => $departmentName ?: 'Warehouse & Packing',
            'issued_to' => $request->issued_to,
            'issue_date' => $request->issue_date,
            'created_by' => Auth::id(),
            'purpose_notes' => $request->purpose_notes,
        ]);

        try {
            $this->service->recordConsumptionIssue($consumption, $request->items);
            toastr()->success("Issue voucher {$consumption->issue_no} logged and stock deducted successfully!");
            return redirect()->route('admin.stationery-supplies.consumptions');
        } catch (\InvalidArgumentException $e) {
            $consumption->delete();
            toastr()->error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Audit Stock Movement Ledger
     */
    public function ledger(Request $request)
    {
        $query = StationeryStockLedger::with(['item', 'creator']);

        if ($request->filled('item_id')) {
            $query->where('stationery_item_id', $request->item_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $ledgers = $query->orderByDesc('id')->paginate(30)->withQueryString();
        $items = StationeryItem::orderBy('name')->get();
        $settings = GeneralSetting::first();

        return view('backend.stationery.ledger', compact('ledgers', 'items', 'settings'));
    }
}
