<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "Total Orders in Database: " . Order::count() . "\n";
echo "Orders grouped by status:\n";
print_r(Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray());

echo "Orders grouped by shipping_method:\n";
print_r(Order::selectRaw('shipping_method, count(*) as total')->groupBy('shipping_method')->pluck('total', 'shipping_method')->toArray());
