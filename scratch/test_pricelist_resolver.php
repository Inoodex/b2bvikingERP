<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Services\Pricing\PricelistResolverService;

echo "--- Testing Step 3.4 PricelistResolverService ---\n";

$product = Product::first();
if (!$product) {
    echo "No product found to test.\n";
    exit;
}

echo "Base Product: " . $product->name . " (MRP: " . $product->price . ")\n";

// Create or update a test Wholesale Pricelist
$pricelist = Pricelist::updateOrCreate(
    ['name' => 'Test Wholesale Tier 2026'],
    [
        'customer_segment' => 'wholesale',
        'status' => 1,
    ]
);

$specialPrice = round($product->price * 0.85, 2); // 15% discount
PricelistItem::updateOrCreate(
    ['pricelist_id' => $pricelist->id, 'product_id' => $product->id],
    ['price' => $specialPrice]
);

echo "Pricelist created: " . $pricelist->name . " with special price kr. " . $specialPrice . "\n";

// Test customer resolution
$wholesaleUser = User::where('customer_segment', 'wholesale')->first();
if (!$wholesaleUser) {
    $wholesaleUser = User::first();
    $wholesaleUser->customer_segment = 'wholesale';
    $wholesaleUser->save();
}

$service = new PricelistResolverService();
$res = $service->resolvePrice($wholesaleUser->id, $product->id);

echo "Resolution Result for User (" . $wholesaleUser->name . ", Segment: " . $wholesaleUser->customer_segment . "):\n";
echo json_encode($res, JSON_PRETTY_PRINT) . "\n";

if ($res['price'] == $specialPrice) {
    echo "\nSUCCESS! Wholesale Tier price kr. " . $specialPrice . " resolved perfectly!\n";
} else {
    echo "\nFAILED! Expected kr. " . $specialPrice . ", got " . $res['price'] . "\n";
}
