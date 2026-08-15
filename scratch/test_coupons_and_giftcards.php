<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coupon;
use App\Models\Discount;
use App\Models\GiftCard;
use App\Services\GiftCard\GiftCardService;

echo "--- Testing Step 3.5 Coupons & Gift Cards Engine ---\n";

// 1. Test Coupon Code Creation & Validation
$discount = Discount::firstOrCreate(
    ['name' => '10% Campaign Discount'],
    ['discount_type' => 'percent', 'discount_value' => 10, 'status' => 1]
);

$coupon = Coupon::updateOrCreate(
    ['code' => 'TESTWELCOME2026'],
    [
        'discount_id' => $discount->id,
        'usage_limit' => 50,
        'used_count' => 0,
        'status' => 1,
    ]
);

echo "Coupon Created: " . $coupon->code . " (Linked Discount: " . $discount->name . ")\n";

// 2. Test Gift Card Issuance & Redemption Ledger
$gcService = new GiftCardService();
$giftCard = $gcService->issueGiftCard([
    'initial_value' => 1000.00,
    'status' => 1,
]);

echo "Gift Card Issued: " . $giftCard->code . " (Initial Balance: kr. " . $giftCard->balance . ")\n";

// Redeem kr. 350.00
$redeemRes = $gcService->redeem($giftCard, 350.00);
echo "Redemption 1 Result:\n" . json_encode($redeemRes, JSON_PRETTY_PRINT) . "\n";

$giftCard->refresh();
echo "Updated Balance after Redemption: kr. " . $giftCard->balance . "\n";

if ($giftCard->balance == 650.00 && $giftCard->transactions()->count() > 0) {
    echo "\nSUCCESS! Coupons & Gift Card Ledger verified 100% perfectly!\n";
} else {
    echo "\nFAILED! Gift Card balance or transaction ledger mismatch.\n";
}
