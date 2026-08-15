<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Services\Credit\CreditValidationService;

$order = Order::with(['user', 'items.product', 'items.variant'])->findOrFail(364);

echo "Testing View Render for Order ID: " . $order->id . " (" . $order->order_no . ")\n";

use Illuminate\Support\Facades\View as ViewFacade;

ViewFacade::share('errors', new \Illuminate\Support\ViewErrorBag());

$creditService = new CreditValidationService();
$creditEvaluation = $creditService->evaluateCreditExposure($order->user_id, (float)$order->total_amount, $order->id);

try {
    $html = view('backend.sales_orders.show', compact('order', 'creditEvaluation'))->render();
    echo "View Rendered Successfully! HTML Length: " . strlen($html) . " bytes.\n";
} catch (\Throwable $e) {
    echo "RENDER ERROR: " . $e->getMessage() . "\nIn " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
