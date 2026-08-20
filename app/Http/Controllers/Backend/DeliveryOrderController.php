<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DeliveryOrderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreDeliveryOrderRequest;
use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Services\DeliveryOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
{
    protected DeliveryOrderService $deliveryOrderService;

    public function __construct(DeliveryOrderService $deliveryOrderService)
    {
        $this->deliveryOrderService = $deliveryOrderService;
    }

    public function index(DeliveryOrderDataTable $dataTable)
    {
        return $dataTable->render('backend.delivery_orders.index');
    }

    public function create(Request $request): View
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

    public function getOrderItems(Request $request): JsonResponse
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
            $alreadyDelivered = (float)\App\Models\DeliveryOrderItem::where('order_item_id', $item->id)
                ->whereHas('deliveryOrder', fn($q) => $q->where('status', '!=', 'cancelled'))
                ->sum('qty_delivered');

            $remaining = max(0, (float)$item->quantity - $alreadyDelivered);

            $items[] = [
                'order_item_id'    => $item->id,
                'product_id'       => $item->product_id,
                'product_name'     => $item->product?->name ?? 'Unknown',
                'variant_id'       => $item->variant_id,
                'variant_name'     => $item->variant ? ($item->variant->color?->name . ' - ' . $item->variant->size?->name) : null,
                'ordered_qty'      => (float)$item->quantity,
                'already_delivered'=> $alreadyDelivered,
                'remaining_qty'    => $remaining,
                'unit_price'       => (float)$item->unit_price,
            ];
        }

        return response()->json([
            'success' => true,
            'order'   => [
                'id'          => $order->id,
                'order_no'    => $order->order_no,
                'customer'    => $order->user ? ($order->user->outlet_name ?: $order->user->name) : 'Guest / Cash',
                'outlet_id'   => $order->outlet_id,
                'total_amount'=> (float)$order->total_amount,
            ],
            'items'   => $items,
        ]);
    }

    public function store(StoreDeliveryOrderRequest $request): RedirectResponse
    {
        $order = Order::findOrFail($request->order_id);
        $deliveryOrder = $this->deliveryOrderService->createDeliveryOrder($request->validated(), Auth::id() ?? 1);

        Toastr::success('Delivery Order Challan created successfully.', 'Success');
        return redirect()->route('admin.delivery-orders.show', $deliveryOrder->id);
    }

    public function show($id): View
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

    public function dispatch($id): RedirectResponse
    {
        $deliveryOrder = DeliveryOrder::with(['order.items', 'items.product', 'items.variant'])->findOrFail($id);

        if ($deliveryOrder->status === 'dispatched' || $deliveryOrder->status === 'shipped') {
            Toastr::warning('This Delivery Order is already dispatched.', 'Warning');
            return redirect()->back();
        }

        $this->deliveryOrderService->dispatchDeliveryOrder($deliveryOrder, Auth::id() ?? 1);

        Toastr::success('Delivery Order dispatched successfully! Inventory stock updated.', 'Dispatched');
        return redirect()->route('admin.delivery-orders.show', $deliveryOrder->id);
    }

    public function printPdf($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'order.user',
            'items.product',
            'items.variant.color',
            'items.variant.size',
            'creator',
            'dispatcher'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('backend.delivery_orders.pdf', compact('deliveryOrder'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Delivery-Order-{$deliveryOrder->delivery_no}.pdf");
    }
}
