<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryOrderService
{
    /**
     * Create a new Delivery Order from validated data.
     */
    public function createDeliveryOrder(array $data, int $userId): DeliveryOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $order = Order::findOrFail($data['order_id']);
            $deliveryNo = \App\Models\DocumentSequence::generateNext('DeliveryOrder');

            $itemsToCreate = [];
            foreach ($data['items'] as $itemData) {
                $qty = (float)($itemData['qty'] ?? $itemData['delivery_qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $orderItemId = $itemData['order_item_id'] ?? null;
                $orderItem = $orderItemId
                    ? OrderItem::where('order_id', $order->id)->where('id', $orderItemId)->first()
                    : OrderItem::where('order_id', $order->id)->where('product_id', $itemData['product_id'])->first();

                if (!$orderItem) {
                    continue;
                }

                $alreadyDelivered = (float)DeliveryOrderItem::where('order_item_id', $orderItem->id)
                    ->whereHas('deliveryOrder', fn($q) => $q->where('status', '!=', 'cancelled'))
                    ->sum('qty_delivered');

                $maxDeliverable = (float)$orderItem->quantity - $alreadyDelivered;
                if ($qty > $maxDeliverable) {
                    throw new \DomainException("Dispatch quantity ({$qty}) exceeds max deliverable quantity ({$maxDeliverable}) for product item #{$orderItem->product_id}.");
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
                throw new \InvalidArgumentException('Please enter a dispatch quantity greater than 0 for at least one item.');
            }

            $deliveryOrder = DeliveryOrder::create([
                'delivery_no'     => $deliveryNo,
                'order_id'        => $order->id,
                'carrier_name'    => $data['carrier_name'] ?? 'Standard Delivery',
                'awb_number'      => $data['awb_number'] ?? ($data['tracking_number'] ?? null),
                'shipping_method' => $data['shipping_method'] ?? 'Road Freight',
                'status'          => 'pending',
                'date'            => $data['delivery_date'] ?? now()->toDateString(),
                'notes'           => $data['notes'] ?? ($data['remarks'] ?? null),
                'created_by'      => $userId,
            ]);

            foreach ($itemsToCreate as $item) {
                $deliveryOrder->items()->create($item);
            }

            return $deliveryOrder;
        });
    }

    /**
     * Dispatch an existing Delivery Order and deduct inventory with Stock Ledger logging.
     */
    public function dispatchDeliveryOrder(DeliveryOrder $deliveryOrder, int $dispatchedBy): DeliveryOrder
    {
        if ($deliveryOrder->status === 'dispatched' || $deliveryOrder->status === 'shipped') {
            return $deliveryOrder;
        }

        return DB::transaction(function () use ($deliveryOrder, $dispatchedBy) {
            $deliveryOrder->update([
                'status'        => 'dispatched',
                'dispatched_by' => $dispatchedBy,
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
                    'product_id'     => $item->product_id,
                    'variant_id'     => $item->variant_id,
                    'outlet_id'      => $deliveryOrder->order?->outlet_id ?? 1,
                    'reference_type' => 'DeliveryOrder',
                    'reference_id'   => $deliveryOrder->id,
                    'in_qty'         => 0,
                    'out_qty'        => (float)$item->qty_delivered,
                    'balance_qty'    => $qtyAfter,
                    'date'           => now()->toDateString(),
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

            return $deliveryOrder->fresh();
        });
    }
}
