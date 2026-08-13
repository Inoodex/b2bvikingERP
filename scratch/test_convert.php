<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SalesQuotation;
use App\Models\Order;
use App\Http\Controllers\Backend\SalesQuotationController;

$quote = SalesQuotation::first();
if ($quote) {
    echo "Quote No before convert: " . $quote->quotation_no . " (Status: " . $quote->status . ")\n";
    $controller = new SalesQuotationController();
    try {
        $controller->convertToOrder($quote);
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    $quote->refresh();
    echo "Quote No after convert: " . $quote->quotation_no . " (Status: " . $quote->status . ")\n";
    $latestOrder = Order::latest()->first();
    if ($latestOrder) {
        echo "Generated Sales Order No: " . $latestOrder->order_no . " with Total: kr. " . $latestOrder->total_amount . "\n";
    }
}
