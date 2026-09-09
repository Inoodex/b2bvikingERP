<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\RecycleBinDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Vendor;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecycleBinController extends Controller
{
    /**
     * Map of supported entities for the Universal Recycle Bin.
     */
    protected const SUPPORTED_ENTITIES = [
        'products' => [
            'label' => 'Products',
            'model' => Product::class,
            'icon'  => 'fas fa-box-open',
        ],
        'orders' => [
            'label' => 'Sales Orders',
            'model' => Order::class,
            'icon'  => 'fas fa-shopping-cart',
        ],
        'invoices' => [
            'label' => 'Sales Invoices',
            'model' => SalesInvoice::class,
            'icon'  => 'fas fa-file-invoice-dollar',
        ],
        'customers' => [
            'label' => 'Customers',
            'model' => User::class,
            'icon'  => 'fas fa-users',
        ],
        'vendors' => [
            'label' => 'Vendors',
            'model' => Vendor::class,
            'icon'  => 'fas fa-truck',
        ],
    ];

    /**
     * Display Recycle Bin dashboard with entity tabs and Yajra DataTable.
     */
    public function index(Request $request, RecycleBinDataTable $dataTable)
    {
        $activeType = $request->query('type', 'products');
        if (!array_key_exists($activeType, self::SUPPORTED_ENTITIES)) {
            $activeType = 'products';
        }

        $singularType = rtrim($activeType, 's');
        if ($activeType === 'invoices') {
            $singularType = 'invoice';
        }

        // Count trashed items across all entities
        $counts = [
            'products'  => Product::onlyTrashed()->count(),
            'orders'    => Order::onlyTrashed()->count(),
            'invoices'  => SalesInvoice::onlyTrashed()->count(),
            'customers' => User::onlyTrashed()->count(),
            'vendors'   => Vendor::onlyTrashed()->count(),
        ];

        return $dataTable->setType($singularType)->render('backend.system.recycle_bin', [
            'entities'   => self::SUPPORTED_ENTITIES,
            'activeType' => $activeType,
            'counts'     => $counts,
        ]);
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restore(Request $request, string $type, int $id)
    {
        $pluralType = $type . 's';
        if ($type === 'invoice') {
            $pluralType = 'invoices';
        }

        $lookupType = array_key_exists($type, self::SUPPORTED_ENTITIES) ? $type : $pluralType;

        if (!array_key_exists($lookupType, self::SUPPORTED_ENTITIES)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "Unsupported entity type: {$type}"], 422);
            }
            Toastr::error("Unsupported entity type: {$type}");
            return redirect()->route('admin.recycle-bin.index');
        }

        $modelClass = self::SUPPORTED_ENTITIES[$lookupType]['model'];
        $record = $modelClass::onlyTrashed()->find($id);

        if (!$record) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "Item not found in Recycle Bin."], 404);
            }
            Toastr::error("Item not found in Recycle Bin.");
            return redirect()->route('admin.recycle-bin.index', ['type' => $lookupType]);
        }

        $record->restore();

        $label = self::SUPPORTED_ENTITIES[$lookupType]['label'];
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "{$label} item restored successfully!"]);
        }
        Toastr::success("{$label} item restored successfully!");
        return redirect()->route('admin.recycle-bin.index', ['type' => $lookupType]);
    }

    /**
     * Restore all soft-deleted items for the active entity.
     */
    public function restoreAll(string $type)
    {
        if (!array_key_exists($type, self::SUPPORTED_ENTITIES)) {
            Toastr::error("Unsupported entity type: {$type}");
            return redirect()->route('admin.recycle-bin.index');
        }

        $modelClass = self::SUPPORTED_ENTITIES[$type]['model'];
        $modelClass::onlyTrashed()->restore();

        $label = self::SUPPORTED_ENTITIES[$type]['label'];
        Toastr::success("All trashed {$label} items restored successfully!");
        return redirect()->route('admin.recycle-bin.index', ['type' => $type]);
    }

    /**
     * Permanently delete a record with foreign key dependency guards.
     */
    public function forceDelete(Request $request, string $type, int $id)
    {
        $pluralType = $type . 's';
        if ($type === 'invoice') {
            $pluralType = 'invoices';
        }

        $lookupType = array_key_exists($type, self::SUPPORTED_ENTITIES) ? $type : $pluralType;

        if (!array_key_exists($lookupType, self::SUPPORTED_ENTITIES)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "Unsupported entity type: {$type}"], 422);
            }
            Toastr::error("Unsupported entity type: {$type}");
            return redirect()->route('admin.recycle-bin.index');
        }

        $modelClass = self::SUPPORTED_ENTITIES[$lookupType]['model'];
        $record = $modelClass::onlyTrashed()->find($id);

        if (!$record) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "Item not found in Recycle Bin."], 404);
            }
            Toastr::error("Item not found in Recycle Bin.");
            return redirect()->route('admin.recycle-bin.index', ['type' => $lookupType]);
        }

        try {
            // Safety checks before permanent hard deletion
            if (($lookupType === 'invoices' || $type === 'invoice') && DB::table('journal_entries')->where('reference_type', 'App\\Models\\SalesInvoice')->where('reference_id', $id)->exists()) {
                throw new Exception("Cannot permanently delete this invoice: Posted General Ledger journal vouchers exist for it.");
            }

            if (($lookupType === 'orders' || $type === 'order') && DB::table('sales_invoices')->where('order_id', $id)->exists()) {
                throw new Exception("Cannot permanently delete this order: Linked sales invoices exist.");
            }

            $record->forceDelete();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => "Item permanently removed from database."]);
            }
            Toastr::success("Item permanently removed from database.");
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "Permanent deletion blocked: " . $e->getMessage()], 422);
            }
            Toastr::error("Permanent deletion blocked: " . $e->getMessage());
        }

        return redirect()->route('admin.recycle-bin.index', ['type' => $lookupType]);
    }
}
