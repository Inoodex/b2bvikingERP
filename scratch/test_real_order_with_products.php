<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\DocumentSequence;
use App\Services\Credit\CreditValidationService;

echo "--- Real Product Order & Credit Validation Verification ---\n";

// 1. Get or Create Test Customer with 10,000 kr. Credit Limit
$testUser = User::where('email', 'testcredit@viking.com')->first();
if (!$testUser) {
    $testUser = User::create([
        'name' => 'Credit Test Partner',
        'email' => 'testcredit@viking.com',
        'password' => bcrypt('password'),
        'status' => 1,
        'role_id' => 2,
        'credit_limit' => 10000.00,
        'customer_segment' => 'wholesale',
        'phone' => '12345678',
        'address' => 'Test Street 100',
    ]);
} else {
    $testUser->credit_limit = 10000.00;
    $testUser->save();
}

$product = Product::first();
if (!$product) {
    echo "No product found in database.\n";
    exit;
}

echo "Selected Product: " . $product->name . " (Price: kr. " . $product->price . ")\n";

// 2. Create Order 1 with Real Product Items (Total kr. 7,000.00)
$soNo1 = DocumentSequence::generateNext('SalesOrder');
$qty1 = 2;
$unitPrice1 = 3500.00;
$total1 = $qty1 * $unitPrice1;

$order1 = Order::create([
    'order_no' => $soNo1,
    'user_id' => $testUser->id,
    'billing_name' => $testUser->name,
    'billing_email' => $testUser->email,
    'billing_phone' => $testUser->phone ?? '12345678',
    'billing_address' => $testUser->address ?? 'Test Street 100',
    'shipping_method' => 'Standard Freight',
    'status' => 'approved',
    'subtotal_amount' => $total1,
    'tax_amount' => 0.00,
    'discount_amount' => 0.00,
    'total_amount' => $total1,
    'paid_amount' => 0.00,
    'due_amount' => $total1,
    'payment_status' => 'unpaid',
    'placed_at' => now(),
]);

OrderItem::create([
    'order_id' => $order1->id,
    'product_id' => $product->id,
    'product_name' => $product->name,
    'quantity' => $qty1,
    'unit_price' => $unitPrice1,
    'line_total' => $total1,
]);

// Attach product line to the earlier SO-202608-0010 if it exists
$oldOrder = Order::where('order_no', 'SO-202608-0010')->first();
if ($oldOrder) {
    if ($oldOrder->items()->count() == 0) {
        OrderItem::create([
            'order_id' => $oldOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 3500.00,
            'line_total' => 7000.00,
        ]);
        echo "Attached Product Lines to existing Order " . $oldOrder->order_no . "!\n";
    }
}

echo "Created Real Order 1: " . $order1->order_no . " with Product Item: " . $product->name . " (Qty: " . $qty1 . ", Total: kr. " . number_format($total1, 2) . ")\n";

// 3. Evaluate Credit for New Order of kr. 5,000.00 (Total Exposure = 7000 + 5000 = 12,000 > 10,000 limit)
$creditService = new CreditValidationService();
$eval = $creditService->evaluateCreditExposure($testUser->id, 5000.00);

echo "New Order Credit Evaluation:\n";
echo "- Customer Credit Limit: kr. " . number_format($eval['credit_limit'], 2) . "\n";
echo "- Current Unpaid Dues: kr. " . number_format($eval['current_dues'], 2) . "\n";
echo "- New Order Total: kr. 5,000.00\n";
echo "- Total Exposure: kr. " . number_format($eval['total_exposure'], 2) . "\n";
echo "- Assigned Status: " . $eval['status'] . "\n";

// Create Credit Hold Order 2 with Real Product Items
$soNo2 = DocumentSequence::generateNext('SalesOrder');
$order2 = Order::create([
    'order_no' => $soNo2,
    'user_id' => $testUser->id,
    'billing_name' => $testUser->name,
    'billing_email' => $testUser->email,
    'billing_phone' => $testUser->phone ?? '12345678',
    'billing_address' => $testUser->address ?? 'Test Street 100',
    'shipping_method' => 'Express Delivery',
    'status' => $eval['status'],
    'subtotal_amount' => 5000.00,
    'tax_amount' => 0.00,
    'discount_amount' => 0.00,
    'total_amount' => 5000.00,
    'paid_amount' => 0.00,
    'due_amount' => 5000.00,
    'payment_status' => 'unpaid',
    'placed_at' => now(),
]);

OrderItem::create([
    'order_id' => $order2->id,
    'product_id' => $product->id,
    'product_name' => $product->name,
    'quantity' => 1,
    'unit_price' => 5000.00,
    'line_total' => 5000.00,
]);

echo "Created Real Order 2 (CREDIT HOLD): " . $order2->order_no . " (ID: " . $order2->id . ") with Product Item: " . $product->name . "!\n";

if ($order2->status === 'credit_hold' && $order2->items()->count() > 0) {
    echo "\nSUCCESS! Real Orders with Product Lines & Credit Hold generated perfectly!\n";
}
