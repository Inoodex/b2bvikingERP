<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DeliveryOrderDataTable;
use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLedger;
use App\Services\OrderNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryOrderController extends Controller
{
    public function index(DeliveryOrderDataTable $dataTable)
    {
        return $dataTable->render('backend.delivery_orders.index');
    }

    public function create(Request $request)
    {
        $selectedOrderId = $request->get('order_id');
        $orders = Order::with('user')
            ->whereIn('status', ['approved', 'processing', 'completed'])
            ->where(function ($q) use ($selectedOrderId) {
                $q->whereNull('fulfillment_status')
                  ->orWhere('fulfillment_status', '!=', 'fully_delivered');
                if ($selectedOrderId) {
                    $q->orWhere('id', $selectedOrderId);
                }
            })
            ->latest()
            ->get();

        return view('backend.delivery_orders.create', compact('orders', 'selectedOrderId'));
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
            $alreadyDelivered = (float)DeliveryOrderItem::where('order_item_id', $item->id)
                ->whereHas('deliveryOrder', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('qty_delivered');

            $maxDeliverable = max(0, (float)$item->quantity - $alreadyDelivered);

            $items[] = [
                'order_item_id'     => $item->id,
                'product_id'        => $item->product_id,
                'product_name'      => $item->product ? $item->product->name : 'Product #' . $item->product_id,
                'variant_id'        => $item->variant_id,
                'variant_name'      => $item->variant ? $item->variant->name : '',
                'color_name'        => ($item->variant && $item->variant->color) ? $item->variant->color->name : '',
                'size_name'         => ($item->variant && $item->variant->size) ? $item->variant->size->name : '',
                'ordered_qty'       => (float)$item->quantity,
                'already_delivered' => $alreadyDelivered,
                'max_deliverable'   => $maxDeliverable,
                'unit_price'        => (float)$item->unit_price,
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
            $deliveryNo = OrderNumberService::generate('DO', DeliveryOrder::class, 'delivery_orders');
            $itemsToCreate = [];

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

                $alreadyDelivered = (float)DeliveryOrderItem::where('order_item_id', $orderItem->id)
                    ->whereHas('deliveryOrder', function ($q) {
                        $q->where('status', '!=', 'cancelled');
                    })
                    ->sum('qty_delivered');

                $maxDeliverable = (float)$orderItem->quantity - $alreadyDelivered;

                if ($qty > $maxDeliverable) {
                    throw new \Exception("Dispatch quantity ({$qty}) exceeds max deliverable quantity ({$maxDeliverable}) for product item.");
                }

                $itemsToCreate[] = [
                    'order_item_id' => $orderItem->id,
                    'product_id'    => $orderItem->product_id,
                    'variant_id'    => $orderItem->variant_id,
                    'qty_delivered' => $qty,
                    'unit_price'    => (float)$orderItem->unit_price,
                ];
            }

            if (empty($itemsToCreate)) {
                throw new \Exception('Please enter a dispatch quantity greater than 0 for at least one item.');
            }

            $deliveryOrder = DeliveryOrder::create([
                'delivery_no'     => $deliveryNo,
                'order_id'        => $order->id,
                'carrier_name'    => $request->carrier_name ?: 'Standard Delivery',
                'awb_number'      => $request->awb_number ?: null,
                'shipping_method' => $request->shipping_method ?: 'Road Freight',
                'status'          => 'pending',
                'date'            => now(),
                'notes'           => $request->notes ?: null,
                'created_by'      => Auth::id(),
            ]);

            foreach ($itemsToCreate as $item) {
                $deliveryOrder->items()->create($item);
            }

            DB::commit();

            Toastr::success('Delivery Order Challan created successfully.', 'Success');
            return redirect()->route('admin.delivery-orders.show', $deliveryOrder->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Order Store Error: ' . $e->getMessage());
            Toastr::error('Failed to create Delivery Order: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'order.user',
            'items.product',
            'items.variant.color',
            'items.variant.size',
            'creator',
            'dispatcher'
        ])->findOrFail($id);

        return view('backend.delivery_orders.show', compact('deliveryOrder'));
    }

    public function dispatch($id)
    {
        $deliveryOrder = DeliveryOrder::with(['order.items', 'items.product', 'items.variant'])->findOrFail($id);

        if ($deliveryOrder->status === 'dispatched' || $deliveryOrder->status === 'shipped') {
            Toastr::warning('This Delivery Order is already dispatched.', 'Warning');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $deliveryOrder->update([
                'status'        => 'dispatched',
                'dispatched_by' => Auth::id(),
            ]);

            foreach ($deliveryOrder->items as $item) {
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                    ],
                    ['quantity' => 0]
                );

                $qtyBefore = (float)$stock->quantity;
                $qtyAfter = max(0, $qtyBefore - (float)$item->qty_delivered);
                $stock->update(['quantity' => $qtyAfter]);

                StockLedger::create([
                    'product_id'      => $item->product_id,
                    'variant_id'      => $item->variant_id,
                    'outlet_id'       => $deliveryOrder->order?->outlet_id ?? null,
                    'reference_type'  => 'DeliveryOrder',
                    'reference_id'    => $deliveryOrder->id,
                    'in_qty'          => 0,
                    'out_qty'         => (float)$item->qty_delivered,
                    'balance_qty'     => $qtyAfter,
                    'date'            => now()->toDateString(),
                ]);
            }

            // Update parent Sales Order fulfillment status
            $parentOrder = $deliveryOrder->order;
            if ($parentOrder) {
                $totalOrdered = (float)$parentOrder->items()->sum('quantity');
                $totalDelivered = (float)DeliveryOrderItem::whereHas('deliveryOrder', function ($q) use ($parentOrder) {
                    $q->where('order_id', $parentOrder->id)->whereIn('status', ['dispatched', 'shipped', 'delivered']);
                })->sum('qty_delivered');

                if ($totalDelivered >= $totalOrdered && $totalOrdered > 0) {
                    $parentOrder->update(['fulfillment_status' => 'fully_delivered']);
                } elseif ($totalDelivered > 0) {
                    $parentOrder->update(['fulfillment_status' => 'partially_delivered']);
                } else {
                    $parentOrder->update(['fulfillment_status' => 'unfulfilled']);
                }
            }

            DB::commit();

            Toastr::success('Delivery Order dispatched successfully! Inventory stock updated.', 'Dispatched');
            return redirect()->route('admin.delivery-orders.show', $deliveryOrder->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Order Dispatch Error: ' . $e->getMessage());
            Toastr::error('Failed to dispatch Delivery Order: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    public function downloadPdf($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'order.user',
            'items.product.unit',
            'items.variant.color',
            'items.variant.size',
            'creator',
            'dispatcher'
        ])->findOrFail($id);

        $generalSetting = \App\Models\GeneralSetting::first();

        $settings = [
            'site_name'       => ($generalSetting && $generalSetting->site_name) ? $generalSetting->site_name : 'B2B Viking ERP',
            'logo'            => ($generalSetting && $generalSetting->logo) ? asset($generalSetting->logo) : null,
            'company_phone'   => ($generalSetting && $generalSetting->contact_phone) ? $generalSetting->contact_phone : '+45 12 34 56 78',
            'company_email'   => ($generalSetting && $generalSetting->contact_email) ? $generalSetting->contact_email : 'sales@b2bviking.dk',
            'company_address' => ($generalSetting && $generalSetting->contact_address) ? $generalSetting->contact_address : 'Vikingvej 42, 2100 Copenhagen, Denmark',
        ];

        $pdf = Pdf::loadView('backend.pdf.delivery_order', compact('deliveryOrder', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Delivery_Challan_' . $deliveryOrder->delivery_no . '.pdf');
    }
}
