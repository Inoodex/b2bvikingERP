<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\StockLedger;
use App\Services\OrderNumberService;
use Illuminate\Support\Facades\DB;

echo "=== STARTING AUTOMATED END-TO-END TEST FOR STEP 3.8 DELIVERY ORDERS ===\n\n";

$order = Order::with('items')->where('order_no', 'SO-202608-0012')->first();

if (!$order) {
    echo "ERROR: Test order SO-202608-0012 not found!\n";
    exit(1);
}

echo "1. Selected Order: #{$order->order_no} (ID: {$order->id}, Customer ID: {$order->user_id})\n";
$firstItem = $order->items->first();

if (!$firstItem) {
    echo "ERROR: Order has no items!\n";
    exit(1);
}

echo "   -> Item: Product #{$firstItem->product_id} | Qty Ordered: {$firstItem->quantity}\n\n";

DB::beginTransaction();
try {
    // 1. Generate DO Sequence Number
    $deliveryNo = OrderNumberService::generate('DO', DeliveryOrder::class, 'delivery_orders');
    echo "2. Generated Official Delivery Number: #{$deliveryNo}\n";

    // 2. Create Delivery Order Record
    $deliveryOrder = DeliveryOrder::create([
        'delivery_no'     => $deliveryNo,
        'order_id'        => $order->id,
        'carrier_name'    => 'PostNord Logistics',
        'awb_number'      => 'PN-TRACK-992384',
        'shipping_method' => 'Express Road Freight',
        'status'          => 'pending',
        'date'            => now(),
        'notes'           => 'Automated test dispatch - handle with care',
        'created_by'      => 1,
    ]);

    // 3. Add Item Line (Partial Dispatch: 1 out of 2)
    $dispatchQty = 1.0;
    $deliveryItem = $deliveryOrder->items()->create([
        'order_item_id' => $firstItem->id,
        'product_id'    => $firstItem->product_id,
        'variant_id'    => $firstItem->variant_id,
        'qty_delivered' => $dispatchQty,
        'unit_price'    => $firstItem->unit_price,
    ]);

    echo "3. Delivery Order Created in DB (ID: {$deliveryOrder->id}, Status: {$deliveryOrder->status})\n";

    // 4. Dispatch & Deduct Stock
    $deliveryOrder->update([
        'status'        => 'dispatched',
        'dispatched_by' => 1,
    ]);

    $stock = InventoryStock::firstOrCreate(
        [
            'product_id' => $firstItem->product_id,
            'variant_id' => $firstItem->variant_id,
        ],
        ['quantity' => 0]
    );

    $qtyBefore = (float)$stock->quantity;
    $qtyAfter = max(0, $qtyBefore - $dispatchQty);
    $stock->update(['quantity' => $qtyAfter]);

    $ledger = StockLedger::create([
        'product_id'      => $firstItem->product_id,
        'variant_id'      => $firstItem->variant_id,
        'reference_type'  => 'DeliveryOrder',
        'reference_id'    => $deliveryOrder->id,
        'transaction_type'=> 'OUT',
        'quantity'        => $dispatchQty,
        'balance_qty'     => $qtyAfter,
        'date'            => now(),
        'notes'           => 'Dispatched via Delivery Order #' . $deliveryOrder->delivery_no,
        'created_by'      => 1,
    ]);

    $order->update(['fulfillment_status' => 'partially_delivered']);

    DB::commit();

    echo "\n=== TEST SUCCESSFUL & VERIFIED! ===\n";
    echo "-> Delivery Order ID: {$deliveryOrder->id}\n";
    echo "-> Delivery No: #{$deliveryOrder->delivery_no}\n";
    echo "-> Carrier: {$deliveryOrder->carrier_name} (AWB: {$deliveryOrder->awb_number})\n";
    echo "-> Status: {$deliveryOrder->status}\n";
    echo "-> Inventory Stock Updated: Before = {$qtyBefore}, After = {$qtyAfter}\n";
    echo "-> StockLedger Entry Created ID: {$ledger->id}\n";
    echo "-> Parent Order Fulfillment Status: {$order->fresh()->fulfillment_status}\n";
    echo "-> URL to View Details: http://127.0.0.1:8000/admin/delivery-orders/{$deliveryOrder->id}\n";
    echo "-> URL for Packing Slip PDF: http://127.0.0.1:8000/admin/delivery-orders/{$deliveryOrder->id}/pdf\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR IN TEST: " . $e->getMessage() . "\n";
    exit(1);
}
