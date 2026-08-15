<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

$order = Order::find(364);
if (!$order) {
    echo "Order 364 does not exist!\n";
    $latest = Order::latest()->take(5)->get();
    echo "Latest 5 orders in DB:\n";
    foreach ($latest as $o) {
        echo "ID: {$o->id} | OrderNo: {$o->order_no} | Total: {$o->total_amount} | UserID: {$o->user_id}\n";
    }
} else {
    echo "Order 364 Details:\n";
    echo json_encode($order->toArray(), JSON_PRETTY_PRINT) . "\n";
    echo "Items Count: " . $order->items()->count() . "\n";
}
