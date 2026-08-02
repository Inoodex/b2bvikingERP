<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\VendorReturn;
use App\Models\VendorReturnItem;
use App\Models\GoodsReceipt;
use App\DataTables\VendorReturnDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;

class VendorReturnController extends Controller
{
    public function index(VendorReturnDataTable $dataTable)
    {
        return $dataTable->render('backend.vendor_return.index');
    }

    public function create(Request $request)
    {
        $grnId = $request->get('grn_id');
        $grn = GoodsReceipt::with(['purchase.vendor', 'items.product', 'items.variant'])->findOrFail($grnId);

        // Check if VendorReturn / Debit Note already exists for this GRN
        $existingReturn = VendorReturn::where('goods_receipt_id', $grn->id)->first();
        if ($existingReturn) {
            Toastr::warning("Vendor Return and Debit Note #{$existingReturn->debit_note_no} has already been issued for this GRN.", 'Duplicate Action Blocked');
            return redirect()->route('admin.vendor-returns.show', $existingReturn->id);
        }

        // Filter items that have rejected_qty > 0
        $rejectedItems = $grn->items->where('rejected_qty', '>', 0);

        return view('backend.vendor_return.create', compact('grn', 'rejectedItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'reason'           => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason'     => 'nullable|string',
        ]);

        $grn = GoodsReceipt::with('purchase')->findOrFail($request->goods_receipt_id);

        // Enterprise Security Guard: Prevent Duplicate Vendor Returns for the same GRN
        $existingReturn = VendorReturn::where('goods_receipt_id', $grn->id)->first();
        if ($existingReturn) {
            Toastr::warning("Vendor Return and Debit Note #{$existingReturn->debit_note_no} was already issued for this GRN.", 'Duplicate Prevented');
            return redirect()->route('admin.vendor-returns.show', $existingReturn->id);
        }

        $vendorReturn = DB::transaction(function () use ($request, $grn) {
            $seq = VendorReturn::whereDate('created_at', now()->toDateString())->count() + 1;
            $returnNo = 'RET-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $debitNoteNo = 'DN-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $vendorReturn = VendorReturn::create([
                'return_no'        => $returnNo,
                'purchase_id'      => $grn->purchase_id,
                'goods_receipt_id' => $grn->id,
                'debit_note_no'    => $debitNoteNo,
                'reason'           => $request->reason,
                'status'           => 'approved', // Auto-approve upon QC return creation
                'approved_by'      => Auth::id(),
            ]);

            foreach ($request->items as $itemData) {
                $qty = (float) $itemData['qty'];
                $price = (float) $itemData['unit_price'];

                VendorReturnItem::create([
                    'vendor_return_id' => $vendorReturn->id,
                    'product_id'       => $itemData['product_id'],
                    'variant_id'       => $itemData['variant_id'] ?? null,
                    'qty'              => $qty,
                    'unit_price'       => $price,
                    'total_amount'     => round($qty * $price, 2),
                    'reason'           => $itemData['reason'] ?? 'QC Rejection',
                ]);
            }

            return $vendorReturn;
        });

        return redirect()->route('admin.vendor-returns.show', $vendorReturn->id)
            ->with('success', "Vendor Return #{$vendorReturn->return_no} and Debit Note #{$vendorReturn->debit_note_no} created successfully.");
    }

    public function show($id)
    {
        $vendorReturn = VendorReturn::with([
            'purchase.vendor',
            'goodsReceipt',
            'approvedBy',
            'items.product',
            'items.variant'
        ])->findOrFail($id);

        return view('backend.vendor_return.show', compact('vendorReturn'));
    }
}
