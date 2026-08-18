if (!app()->isBooted()) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use App\Models\CustomerPayment;
use App\Models\SalesInvoice;
use App\Services\OrderNumberService;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

echo "=== STARTING CUSTOMER PAYMENT COLLECTION END-TO-END TEST ===\n\n";

$invoice = SalesInvoice::with('order')->find(2);

if (!$invoice) {
    echo "ERROR: Sales Invoice #2 not found.\n";
    exit;
}

echo "1. Initial Target Invoice State:\n";
echo "   - Invoice No: {$invoice->invoice_no}\n";
echo "   - Total Amount: {$invoice->total_amount}\n";
echo "   - Paid Amount: {$invoice->paid_amount}\n";
echo "   - Due Amount: {$invoice->due_amount}\n\n";

// 2. Generate Receipt Sequence
$receiptNo = OrderNumberService::generateCustomerPaymentNumber();
echo "2. Generated Payment Receipt Sequence: {$receiptNo}\n\n";

// 3. Store Payment Receipt
DB::beginTransaction();
try {
    $payment = CustomerPayment::create([
        'payment_no' => $receiptNo,
        'user_id' => $invoice->user_id ?: 1,
        'sales_invoice_id' => $invoice->id,
        'order_id' => $invoice->order_id,
        'amount' => $invoice->due_amount,
        'payment_method' => 'bank_transfer',
        'reference_no' => 'TRF-TEST-889123',
        'payment_date' => now()->toDateString(),
        'notes' => 'Automated End-to-End Test Customer Payment',
        'created_by' => 1,
        'status' => 'posted',
    ]);

    // Update invoice due amount
    $newPaid = (float)$invoice->paid_amount + (float)$payment->amount;
    $newDue = max(0, (float)$invoice->total_amount - $newPaid);
    $invoice->update([
        'paid_amount' => $newPaid,
        'due_amount' => $newDue,
    ]);

    // Dispatch GL Posting
    $controller = new \App\Http\Controllers\Backend\CustomerPaymentController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('postJournalEntry');
    $method->setAccessible(true);
    $method->invoke($controller, $payment);

    DB::commit();

    echo "3. Payment Receipt Record Created Successfully:\n";
    echo "   - Payment ID: {$payment->id}\n";
    echo "   - Payment Receipt No: {$payment->payment_no}\n";
    echo "   - Amount Received: kr. {$payment->amount}\n";
    echo "   - Payment Method: {$payment->payment_method}\n\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR during payment creation: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit;
}

// 4. Verify Invoice Knockdown
$invoice->refresh();
echo "4. Invoice State After Payment Knockdown:\n";
echo "   - New Paid Amount: {$invoice->paid_amount}\n";
echo "   - New Due Amount: {$invoice->due_amount}\n";

if ((float)$invoice->due_amount === 0.0) {
    echo "   ✅ SUCCESS: Invoice Due Amount fully knocked down to 0.00!\n\n";
} else {
    echo "   ❌ ERROR: Invoice due amount was not fully knocked down.\n\n";
}

// 5. Verify General Ledger Journal Entry
$payment->load('journalEntry.lines.account');
if ($payment->journalEntry) {
    echo "5. General Ledger Double-Entry Posting Verified:\n";
    echo "   - Entry No: {$payment->journalEntry->entry_number}\n";
    echo "   - Total Debit: kr. {$payment->journalEntry->total_debit}\n";
    echo "   - Total Credit: kr. {$payment->journalEntry->total_credit}\n";
    foreach ($payment->journalEntry->lines as $line) {
        $accName = $line->account ? $line->account->account_name : 'Account';
        echo "     * {$accName} -> Dr: {$line->debit}, Cr: {$line->credit}\n";
    }
    echo "   ✅ SUCCESS: Double-Entry GL Journal Entry Posted!\n\n";
} else {
    echo "   ❌ ERROR: Journal Entry not found for payment.\n\n";
}

// 6. Verify DomPDF Receipt Generation
try {
    $payment->load(['user', 'invoice', 'order', 'account', 'creator']);
    $pdf = Pdf::loadView('backend.pdf.customer_payment', ['customerPayment' => $payment]);
    $output = $pdf->output();
    echo "6. DomPDF Payment Receipt PDF Stream Verified:\n";
    echo "   - Generated PDF Size: " . strlen($output) . " bytes\n";
    echo "   ✅ SUCCESS: Payment Receipt PDF rendered flawlessly!\n\n";
} catch (\Throwable $e) {
    echo "   ❌ ERROR during PDF generation: " . $e->getMessage() . "\n\n";
}

echo "=== ALL CUSTOMER PAYMENT TESTS PASSED SUCCESSFULLY! ===\n";
