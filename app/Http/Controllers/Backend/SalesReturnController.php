<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SalesReturnDataTable;
use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockLedger;
use App\Services\OrderNumberService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesReturnController extends Controller
{
    public function index(SalesReturnDataTable $dataTable)
    {
        return $dataTable->render('backend.sales_returns.index');
    }

    public function create()
    {
        $orders = Order::with('user')
            ->whereIn('status', ['approved', 'processing', 'completed'])
            ->latest()
            ->get();

        return view('backend.sales_returns.create', compact('orders'));
    }

    public function getOrderItems(Request $request)
    {
        $orderId = $request->get('order_id');
        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'Order ID is required']);
        }

        $order = Order::with(['items.product', 'items.variant.color', 'items.variant.size'])->find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found']);
        }

        $items = [];
        foreach ($order->items as $item) {
            $alreadyReturned = (float)SalesReturnItem::where('order_item_id', $item->id)
                ->whereHas('salesReturn', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('qty');

            $maxReturnable = max(0, (float)$item->quantity - $alreadyReturned);

            $items[] = [
                'order_item_id' => $item->id,
                'product_id'    => $item->product_id,
                'product_name'  => $item->product ? $item->product->name : 'Product #' . $item->product_id,
                'variant_id'    => $item->variant_id,
                'variant_name'  => $item->variant ? $item->variant->name : '',
                'color_name'    => ($item->variant && $item->variant->color) ? $item->variant->color->name : '',
                'size_name'     => ($item->variant && $item->variant->size) ? $item->variant->size->name : '',
                'ordered_qty'   => (float)$item->quantity,
                'already_returned' => $alreadyReturned,
                'max_returnable'   => $maxReturnable,
                'unit_price'    => (float)$item->unit_price,
            ];
        }

        return response()->json([
            'success' => true,
            'items'   => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'items'    => 'required|array|min:1',
        ]);

        $order = Order::with('items')->findOrFail($request->order_id);

        DB::beginTransaction();
        try {
            $returnNo = OrderNumberService::generate('RET', SalesReturn::class, 'sales_returns');
            $itemsToCreate = [];
            $totalRefundAmount = 0.00;

            foreach ($request->items as $itemData) {
                $qty = (float)($itemData['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('id', $itemData['order_item_id'])
                    ->first();

                if (!$orderItem) {
                    continue;
                }

                $alreadyReturned = (float)SalesReturnItem::where('order_item_id', $orderItem->id)
                    ->whereHas('salesReturn', function ($q) {
                        $q->where('status', '!=', 'cancelled');
                    })
                    ->sum('qty');

                $maxReturnable = (float)$orderItem->quantity - $alreadyReturned;

                if ($qty > $maxReturnable) {
                    throw new \Exception("Return quantity ({$qty}) exceeds max returnable ({$maxReturnable}) for product item.");
                }

                $lineRefund = round($qty * (float)$orderItem->unit_price, 2);
                $totalRefundAmount += $lineRefund;

                $itemsToCreate[] = [
                    'order_item_id' => $orderItem->id,
                    'product_id'    => $orderItem->product_id,
                    'variant_id'    => $orderItem->variant_id,
                    'qty'           => $qty,
                    'reason'        => $itemData['reason'] ?? 'Damaged / Customer Return',
                    'disposition'   => $itemData['disposition'] ?? 'restock',
                ];
            }

            if (empty($itemsToCreate)) {
                throw new \Exception('Please enter a return quantity greater than 0 for at least one item.');
            }

            $salesReturn = SalesReturn::create([
                'return_no'       => $returnNo,
                'order_id'        => $order->id,
                'refund_amount'   => $totalRefundAmount,
                'refund_method'   => 'credit_note',
                'return_to_stock' => $request->has('return_to_stock') ? (bool)$request->return_to_stock : true,
                'status'          => 'pending',
                'created_by'      => Auth::id(),
            ]);

            foreach ($itemsToCreate as $item) {
                $salesReturn->items()->create($item);
            }

            DB::commit();

            Toastr::success('Customer Sales Return request created successfully.', 'Success');
            return redirect()->route('admin.sales-returns.show', $salesReturn->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sales Return Store Error: ' . $e->getMessage());
            Toastr::error('Failed to create Sales Return: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $salesReturn = SalesReturn::with([
            'order.user',
            'items.product',
            'items.variant.color',
            'items.variant.size',
            'creditNote',
            'approver',
            'creator'
        ])->findOrFail($id);

        return view('backend.sales_returns.show', compact('salesReturn'));
    }

    public function approve($id)
    {
        $salesReturn = SalesReturn::with(['order', 'items.product', 'items.variant'])->findOrFail($id);

        if ($salesReturn->status === 'approved') {
            Toastr::warning('This Sales Return is already approved.', 'Warning');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $salesReturn->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
            ]);

            foreach ($salesReturn->items as $item) {
                // Item-level disposition check: Restock only if item disposition is 'restock'
                $isItemRestock = ($item->disposition === 'restock' || (empty($item->disposition) && $salesReturn->return_to_stock));
                
                if ($isItemRestock) {
                    $stock = InventoryStock::firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id,
                        ],
                        ['quantity' => 0]
                    );

                    $qtyBefore = (float)$stock->quantity;
                    $qtyAfter = $qtyBefore + (float)$item->qty;
                    $stock->update(['quantity' => $qtyAfter]);

                    StockLedger::create([
                        'product_id'      => $item->product_id,
                        'variant_id'      => $item->variant_id,
                        'reference_type'  => 'SalesReturn',
                        'reference_id'    => $salesReturn->id,
                        'transaction_type'=> 'IN',
                        'quantity'        => (float)$item->qty,
                        'balance_qty'     => $qtyAfter,
                        'notes'           => 'Restocked from Customer Return #' . $salesReturn->return_no,
                        'created_by'      => Auth::id(),
                    ]);
                }
            }

            $creditNoteNo = OrderNumberService::generate('CN', CreditNote::class, 'credit_notes');

            $creditNote = CreditNote::create([
                'credit_note_no'    => $creditNoteNo,
                'sales_return_id'   => $salesReturn->id,
                'customer_id'       => $salesReturn->order ? $salesReturn->order->user_id : null,
                'amount'            => $salesReturn->refund_amount,
                'settled_amount'    => 0.00,
                'remaining_amount'  => $salesReturn->refund_amount,
                'settlement_status' => 'unsettled',
                'notes'             => 'Auto-generated Credit Note from Customer Return #' . $salesReturn->return_no,
                'created_by'        => Auth::id(),
            ]);

            $salesReturn->update(['credit_note_no' => $creditNoteNo]);

            DB::commit();

            Toastr::success('Sales Return approved! Credit Note ' . $creditNoteNo . ' issued successfully.', 'Approved');
            return redirect()->route('admin.sales-returns.show', $salesReturn->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sales Return Approval Error: ' . $e->getMessage());
            Toastr::error('Failed to approve Sales Return: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }
}
