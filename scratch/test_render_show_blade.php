<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Services\Credit\CreditValidationService;

$order = Order::with(['user', 'items.product', 'items.variant'])->findOrFail(374);

echo "Testing View Render for Order ID: " . $order->id . " (" . $order->order_no . ")\n";

use Illuminate\Support\Facades\View as ViewFacade;

ViewFacade::share('errors', new \Illuminate\Support\ViewErrorBag());

$creditService = new CreditValidationService();
$creditEvaluation = $creditService->evaluateCreditExposure($order->user_id, (float)$order->total_amount, $order->id);

try {
    $order->reconcileTotals();
    $order->refresh();
    $order->load(['items.product', 'items.variant.color', 'items.variant.size', 'items.vendor', 'user', 'payments.receipts']);
    $items = $order->items;
    $piInfo = \App\Support\PiInfoSupport::prepare($order->pi_info, $items, 'quantity');
    $piTotals = \App\Support\PiInfoSupport::summarize($piInfo);
    $hasSavedPiInfo = \App\Support\PiInfoSupport::hasContent($order->pi_info);
    $creditService = new CreditValidationService();
    $creditEvaluation = $creditService->evaluateCreditExposure($order->user_id, (float)$order->total_amount, $order->id);

    $html = view('backend.orders.show', compact('order', 'piInfo', 'piTotals', 'hasSavedPiInfo', 'items', 'creditEvaluation'))->render();
    echo "View Rendered Successfully! HTML Length: " . strlen($html) . " bytes.\n";
} catch (\Throwable $e) {
    echo "RENDER ERROR: " . $e->getMessage() . "\nIn " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
