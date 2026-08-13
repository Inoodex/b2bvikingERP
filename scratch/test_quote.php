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

$customer = User::first();
$product = Product::first();

if (!$customer || !$product) {
    echo "No customer or product found.\n";
    exit;
}

$sqNo = DocumentSequence::generateNext('SalesQuotation');
echo "Generated Quotation No: " . $sqNo . "\n";

$quote = SalesQuotation::create([
    'quotation_no' => $sqNo,
    'customer_id' => $customer->id,
    'valid_until' => now()->addDays(15),
    'status' => 'draft',
    'subtotal_amount' => 1500.00,
    'tax_amount' => 375.00,
    'discount_amount' => 50.00,
    'total_amount' => 1825.00,
    'notes' => 'Test Sales Quotation',
    'created_by' => $customer->id,
]);

SalesQuotationItem::create([
    'sales_quotation_id' => $quote->id,
    'product_id' => $product->id,
    'qty' => 2,
    'unit_price' => 750.00,
]);

echo "Created Quote ID: " . $quote->id . " with Total: kr. " . $quote->total_amount . "\n";
