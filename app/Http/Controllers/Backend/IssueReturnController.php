<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\IssueReturn;
use App\Models\IssueReturnItem;
use App\Models\InventoryStock;
use App\Models\StockLedger;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderNumberService;
use App\Support\AuditLogSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;

class IssueReturnController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = IssueReturn::with(['issue', 'outlet', 'approvedBy'])
                ->select('issue_returns.*');

            if ($request->filled('status')) {
                $data->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $data->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $data->whereDate('created_at', '<=', $request->date_to);
            }

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d h:i A') : '-';
                })
                ->addColumn('issue_no', function ($row) {
                    return $row->issue ? $row->issue->issue_no : '-';
                })
                ->addColumn('outlet_name', function ($row) {
                    if ($row->outlet) {
                        return $row->outlet->name . ($row->outlet->outlet_name ? ' (' . $row->outlet->outlet_name . ')' : '');
                    }
                    return 'Main Warehouse';
                })
                ->addColumn('refund_amount', function ($row) {
                    return number_format($row->refund_amount, 2);
                })
                ->addColumn('status', function ($row) {
                    if ($row->status === 'approved') {
                        return '<span class="badge badge-success">Approved</span>';
                    } elseif ($row->status === 'cancelled') {
                        return '<span class="badge badge-danger">Cancelled</span>';
                    }
                    return '<span class="badge badge-warning">Pending</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.issue-returns.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.issue_return.index');
    }

    public function create()
    {
        $issues = Issue::with('outlet')->latest()->get();
        return view('backend.issue_return.create', compact('issues'));
    }

    public function getIssueItems(Request $request)
    {
        $request->validate([
            'issue_id' => 'required|exists:issues,id'
        ]);

        $issue = Issue::with(['items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($request->issue_id);

        $items = $issue->items->map(function ($item) use ($issue) {
            // Find how many have already been returned for this issue and product/variant
            $alreadyReturned = IssueReturnItem::whereHas('issueReturn', function ($q) use ($issue) {
                $q->where('issue_id', $issue->id)->where('status', 'approved');
            })
            ->where('product_id', $item->product_id)
            ->where('variant_id', $item->variant_id)
            ->sum('quantity');

            // Find the unit price from the linked order (if any)
            $unitPrice = 0.00;
            if ($issue->order_id) {
                $orderItem = \App\Models\OrderItem::where('order_id', $issue->order_id)
                    ->where('product_id', $item->product_id)
                    ->where('variant_id', $item->variant_id)
                    ->first();
                if ($orderItem) {
                    $unitPrice = (float) $orderItem->unit_price;
                }
            }

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : 'Deleted Product',
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant ? $item->variant->name : null,
                'color_name' => $item->variant && $item->variant->color ? $item->variant->color->name : null,
                'size_name' => $item->variant && $item->variant->size ? $item->variant->size->name : null,
                'issued_qty' => (int) $item->quantity,
                'already_returned' => (int) $alreadyReturned,
                'remaining_qty' => max(0, (int) $item->quantity - (int) $alreadyReturned),
                'unit_price' => $unitPrice,
            ];
        });

        return response()->json([
            'items' => $items,
            'order_id' => $issue->order_id,
            'outlet_id' => $issue->outlet_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'issue_id' => 'required|exists:issues,id',
            'note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:good,damaged',
        ]);

        $issue = Issue::findOrFail($request->issue_id);

        DB::beginTransaction();
        try {
            // Generate return number
            $returnNo = OrderNumberService::generate('RET', IssueReturn::class, 'issue_returns');

            $totalQty = 0;
            $refundAmount = 0.00;

            // First pass validation: check remaining quantity and calculate total qty/refund
            foreach ($request->items as $item) {
                // Find issued qty
                $issuedItem = IssueItem::where('issue_id', $issue->id)
                    ->where('product_id', $item['product_id'])
                    ->where('variant_id', $item['variant_id'] ?: null)
                    ->first();

                if (!$issuedItem) {
                    throw new \Exception("Item " . $item['product_id'] . " was not part of original Issue.");
                }

                // Find already returned
                $alreadyReturned = IssueReturnItem::whereHas('issueReturn', function ($q) use ($issue) {
                    $q->where('issue_id', $issue->id)->where('status', 'approved');
                })
                ->where('product_id', $item['product_id'])
                ->where('variant_id', $item['variant_id'] ?: null)
                ->sum('quantity');

                $remainingQty = max(0, $issuedItem->quantity - $alreadyReturned);

                if ($item['quantity'] > $remainingQty) {
                    $productName = Product::find($item['product_id'])->name ?? 'Product';
                    throw new \Exception("Cannot return more than remaining issued quantity for '{$productName}'. Remaining: {$remainingQty}, Requested: {$item['quantity']}");
                }

                // Unit price from Order
                $unitPrice = 0.00;
                if ($issue->order_id) {
                    $orderItem = \App\Models\OrderItem::where('order_id', $issue->order_id)
                        ->where('product_id', $item['product_id'])
                        ->where('variant_id', $item['variant_id'] ?: null)
                        ->first();
                    if ($orderItem) {
                        $unitPrice = (float) $orderItem->unit_price;
                    }
                }

                $totalQty += $item['quantity'];
                $refundAmount += $item['quantity'] * $unitPrice;
            }

            // Create Master Return (Pending status)
            $issueReturn = IssueReturn::create([
                'return_no' => $returnNo,
                'issue_id' => $issue->id,
                'order_id' => $issue->order_id,
                'outlet_id' => $issue->outlet_id,
                'refund_amount' => $refundAmount,
                'note' => $request->note,
                'status' => 'pending',
            ]);

            // Save items
            foreach ($request->items as $item) {
                $unitPrice = 0.00;
                if ($issue->order_id) {
                    $orderItem = \App\Models\OrderItem::where('order_id', $issue->order_id)
                        ->where('product_id', $item['product_id'])
                        ->where('variant_id', $item['variant_id'] ?: null)
                        ->first();
                    if ($orderItem) {
                        $unitPrice = (float) $orderItem->unit_price;
                    }
                }

                IssueReturnItem::create([
                    'issue_return_id' => $issueReturn->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?: null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'condition' => $item['condition'],
                ]);
            }

            // Log activity
            AuditLogSupport::log([
                'module' => 'issues',
                'action' => 'return_created',
                'entity_type' => 'issue_return',
                'entity_id' => $issueReturn->id,
                'reference_no' => $issueReturn->return_no,
                'description' => 'Created return request for Issue #' . $issue->issue_no,
                'new_values' => [
                    'return_no' => $issueReturn->return_no,
                    'issue_id' => $issue->id,
                    'total_qty' => $totalQty,
                    'refund_amount' => $refundAmount,
                ]
            ]);

            DB::commit();

            Toastr::success('Return request created successfully and is pending approval.');
            return redirect()->route('admin.issue-returns.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Issue Return creation failed: ' . $e->getMessage());
            Toastr::error('Failed to create return: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $return = IssueReturn::with(['issue', 'outlet', 'approvedBy', 'items.product', 'items.variant.color', 'items.variant.size'])
            ->findOrFail($id);
        return view('backend.issue_return.show', compact('return'));
    }

    public function approve($id)
    {
        $issueReturn = IssueReturn::with(['items', 'issue'])->findOrFail($id);

        if ($issueReturn->status !== 'pending') {
            Toastr::error('Only pending return requests can be approved.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            // Process each item: Stock recovery + Ledger entry
            foreach ($issueReturn->items as $item) {
                // If condition is good, add to stock
                if ($item->condition === 'good') {
                    // Update main stock (outlet 1)
                    $stock = InventoryStock::firstOrCreate([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id ?: null,
                        'outlet_id' => 1 // Main Warehouse
                    ], [
                        'quantity' => 0
                    ]);

                    $stock->increment('quantity', $item->quantity);

                    // Create Stock Ledger Entry (In movement)
                    StockLedger::create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id ?: null,
                        'outlet_id' => 1,
                        'reference_type' => 'issue_return',
                        'reference_id' => $issueReturn->id,
                        'in_qty' => $item->quantity,
                        'out_qty' => 0,
                        'balance_qty' => $stock->quantity,
                        'date' => now()
                    ]);
                } else {
                    // If damaged, we don't increment main stock, but we can write a ledger entry with in_qty = 0 to track it
                    StockLedger::create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id ?: null,
                        'outlet_id' => 1,
                        'reference_type' => 'issue_return_damaged',
                        'reference_id' => $issueReturn->id,
                        'in_qty' => 0,
                        'out_qty' => 0,
                        'balance_qty' => InventoryStock::where([
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id ?: null,
                            'outlet_id' => 1
                        ])->value('quantity') ?? 0,
                        'date' => now()
                    ]);
                }
            }

            // Financial reconciliation (Order due/refund)
            if ($issueReturn->order_id && $issueReturn->refund_amount > 0) {
                $order = Order::findOrFail($issueReturn->order_id);
                $refundLeft = (float) $issueReturn->refund_amount;

                // Adjust due amount first
                if ($order->due_amount > 0) {
                    $dueReduction = min($order->due_amount, $refundLeft);
                    $order->due_amount -= $dueReduction;
                    $refundLeft -= $dueReduction;
                }

                // Adjust total order amount
                $order->total_amount = max(0, $order->total_amount - $issueReturn->refund_amount);

                // If there's still refund left, we generate a refund payment entry (negated amount)
                if ($refundLeft > 0) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => -$refundLeft,
                        'payment_method' => 'refund',
                        'transaction_id' => 'REF-' . strtoupper(uniqid()),
                        'note' => 'Refund generated from Return #' . $issueReturn->return_no,
                    ]);

                    $order->paid_amount = max(0, $order->paid_amount - $refundLeft);
                }

                // Recalculate payment status
                if ($order->due_amount <= 0) {
                    $order->payment_status = 'paid';
                } elseif ($order->paid_amount > 0) {
                    $order->payment_status = 'partial';
                } else {
                    $order->payment_status = 'pending';
                }

                $order->save();
            }

            // Approve Return Master
            $issueReturn->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            // Log activity
            AuditLogSupport::log([
                'module' => 'issues',
                'action' => 'return_approved',
                'entity_type' => 'issue_return',
                'entity_id' => $issueReturn->id,
                'reference_no' => $issueReturn->return_no,
                'description' => 'Approved return #' . $issueReturn->return_no,
                'new_values' => [
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                ]
            ]);

            DB::commit();
            Toastr::success('Return request approved. Stock and accounts updated.');
            return redirect()->route('admin.issue-returns.show', $issueReturn->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Issue Return approval failed: ' . $e->getMessage());
            Toastr::error('Approval failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function cancel($id)
    {
        $issueReturn = IssueReturn::findOrFail($id);

        if ($issueReturn->status !== 'pending') {
            Toastr::error('Only pending return requests can be cancelled.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $issueReturn->update([
                'status' => 'cancelled',
            ]);

            // Log activity
            AuditLogSupport::log([
                'module' => 'issues',
                'action' => 'return_cancelled',
                'entity_type' => 'issue_return',
                'entity_id' => $issueReturn->id,
                'reference_no' => $issueReturn->return_no,
                'description' => 'Cancelled return #' . $issueReturn->return_no,
                'new_values' => [
                    'status' => 'cancelled',
                ]
            ]);

            DB::commit();
            Toastr::success('Return request cancelled.');
            return redirect()->route('admin.issue-returns.show', $issueReturn->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Issue Return cancellation failed: ' . $e->getMessage());
            Toastr::error('Cancellation failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
