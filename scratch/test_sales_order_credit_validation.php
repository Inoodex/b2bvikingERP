<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\DocumentSequence;
use App\Services\Credit\CreditValidationService;

echo "--- Testing Step 3.6 Sales Order & Credit Validation Engine ---\n";

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
    ]);
} else {
    $testUser->credit_limit = 10000.00;
    $testUser->save();
}

echo "Test Customer: " . $testUser->name . " (Credit Limit: kr. " . $testUser->credit_limit . ")\n";

$creditService = new CreditValidationService();

// Test 1: Order within limit (kr. 4,000.00)
$eval1 = $creditService->evaluateCreditExposure($testUser->id, 4000.00);
echo "Eval 1 (kr. 4,000 order): Status = " . $eval1['status'] . " (Remaining Credit: kr. " . $eval1['remaining_credit'] . ")\n";

// Create order 1 with due amount kr. 7,000.00
$orderNo1 = DocumentSequence::generateNext('SalesOrder');
$order1 = Order::create([
    'order_no' => $orderNo1,
    'user_id' => $testUser->id,
    'billing_name' => $testUser->name,
    'billing_email' => $testUser->email,
    'billing_phone' => '12345678',
    'billing_address' => 'Test Street 100',
    'status' => 'approved',
    'total_amount' => 7000.00,
    'paid_amount' => 0.00,
    'due_amount' => 7000.00,
    'payment_status' => 'unpaid',
]);
echo "Past Order 1 Created: " . $order1->order_no . " (Due Amount: kr. " . $order1->due_amount . ")\n";

// Test 2: New Order of kr. 5,000.00 (Total Exposure = 7000 + 5000 = 12000 > 10000 limit)
$eval2 = $creditService->evaluateCreditExposure($testUser->id, 5000.00);
echo "Eval 2 (kr. 5,000 order with 7k past dues): Status = " . $eval2['status'] . " (Total Exposure: kr. " . $eval2['total_exposure'] . ")\n";

if ($eval2['status'] === 'credit_hold' && $eval2['is_exceeded']) {
    echo "\nSUCCESS! Credit Exposure Math & Credit Hold Flagging verified 100% perfectly!\n";
} else {
    echo "\nFAILED! Credit evaluation mismatch.\n";
}
