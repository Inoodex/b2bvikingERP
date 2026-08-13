<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\DocumentSequence;
use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use App\Http\Controllers\Backend\SalesQuotationController;

$customer = User::first();
$product = Product::first();

$sqNo = DocumentSequence::generateNext('SalesQuotation');
$quote = SalesQuotation::create([
    'quotation_no' => $sqNo,
    'customer_id' => $customer->id,
    'valid_until' => now()->addDays(15),
    'status' => 'draft',
    'subtotal_amount' => 1000.00,
    'tax_amount' => 250.00,
    'discount_amount' => 0.00,
    'total_amount' => 1250.00,
    'created_by' => $customer->id,
]);

SalesQuotationItem::create([
    'sales_quotation_id' => $quote->id,
    'product_id' => $product->id,
    'qty' => 1,
    'unit_price' => 1000.00,
]);

echo "Fresh Quote Created: " . $quote->quotation_no . " (Status: " . $quote->status . ")\n";

try {
    $soNo = DocumentSequence::generateNext('SalesOrder');
    $customer = $quote->customer;

    $order = App\Models\Order::create([
        'order_no' => $soNo,
        'user_id' => $quote->customer_id,
        'billing_name' => $customer?->name ?? 'Customer',
        'billing_email' => $customer?->email ?? 'customer@example.com',
        'billing_phone' => $customer?->phone ?? '00000000',
        'billing_address' => $customer?->address ?? 'N/A',
        'subtotal_amount' => $quote->subtotal_amount,
        'tax_amount' => $quote->tax_amount,
        'discount_amount' => $quote->discount_amount,
        'total_amount' => $quote->total_amount,
        'paid_amount' => 0,
        'due_amount' => $quote->total_amount,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'placed_at' => now(),
    ]);

    foreach ($quote->items as $item) {
        App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id,
            'product_name' => $item->product?->name ?? 'Product',
            'unit_price' => $item->unit_price,
            'quantity' => $item->qty,
            'line_total' => round($item->qty * $item->unit_price, 2),
        ]);
    }

    $quote->update(['status' => 'converted']);
    echo "SUCCESS! Created Order: " . $order->order_no . " | Quote status after: " . $quote->status . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
