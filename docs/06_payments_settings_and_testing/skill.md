# 🧠 Skill: Payments Engine, Feature Toggles & Maintenance Playbook
**Category:** `06_payments_settings_and_testing`  
**Standard:** Enterprise Execution Playbook (Namecheap Shared-Hosting Architecture)

---

## 1. Native Direct PayPal REST API v2 Engine (Zero-Package, Zero-Webhook)

This architecture uses Laravel's native `Illuminate\Support\Facades\Http` client. It runs reliably on Namecheap Shared Hosting without requiring VPS daemons, supervisor workers, or open webhook ports.

### 1.1 OAuth2 Token Acquisition & Cache Architecture
```text
INPUT: PayPalClientId, PayPalClientSecret, PayPalMode ('sandbox' | 'live')
OUTPUT: string $bearerToken

1. Cache Key: "paypal_access_token_" . md5($clientId)
2. IF Cache::has($key):
     RETURN Cache::get($key);

3. $endpoint = ($mode === 'live') 
     ? 'https://api-m.paypal.com/v1/oauth2/token' 
     : 'https://api-m.sandbox.paypal.com/v1/oauth2/token';

4. $response = Http::withBasicAuth($clientId, $clientSecret)
     ->asForm()
     ->post($endpoint, ['grant_type' => 'client_credentials']);

5. IF $response->failed():
     THROW PaymentGatewayException("Failed to authenticate with PayPal: " . $response->body());

6. $data = $response->json();
7. $expiresIn = max(60, ($data['expires_in'] ?? 3600) - 120);
8. Cache::put($key, $data['access_token'], $expiresIn);
9. RETURN $data['access_token'];
```

### 1.2 Native Order Creation Routine
```text
INPUT: SalesInvoice $invoice, string $currency = 'DKK'
OUTPUT: string $approvalUrl

1. $token = $this->getAccessToken();
2. $endpoint = $this->getBaseUrl() . '/v2/checkout/orders';

3. $payload = [
     "intent" => "CAPTURE",
     "purchase_units" => [
       [
         "reference_id" => "INV-" . $invoice->id,
         "description"  => "Payment for Invoice #" . $invoice->invoice_number,
         "custom_id"    => (string) $invoice->id,
         "amount" => [
           "currency_code" => $currency,
           "value"         => number_format((float)$invoice->due_amount, 2, '.', '')
         ]
       ]
     ],
     "application_context" => [
       "brand_name"          => "Copenhagen Tourist Point",
       "locale"              => "da-DK",
       "landing_page"        => "BILLING",
       "user_action"         => "PAY_NOW",
       "return_url"          => route('admin.paypal.success'),
       "cancel_url"          => route('admin.paypal.cancel')
     ]
   ];

4. $response = Http::withToken($token)
     ->withHeaders(['Content-Type' => 'application/json'])
     ->post($endpoint, $payload);

5. $data = $response->json();
6. Extract 'approve' link from $data['links']:
   $approveUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'];

7. Record PaymentTransaction (status: 'pending', external_reference: $data['id'], order_id: $invoice->order_id, invoice_id: $invoice->id);
8. RETURN $approveUrl;
```

### 1.3 Synchronous Return URL Capture & GL Settlement Routine
When PayPal redirects the customer back to `route('admin.paypal.success', ['token' => $orderId])`:
```text
INPUT: Request $request (string token / orderId)

1. $orderId = $request->query('token');
2. IF empty($orderId):
     THROW InvalidRequestException("Missing PayPal Order Token");

3. START DB::transaction()
4. // 1. Double-Submission Idempotency Check
   $existingTxn = PaymentTransaction::where('external_reference', $orderId)
     ->where('status', 'captured')
     ->lockForUpdate()
     ->first();

   IF ($existingTxn):
     // Already settled, prevent duplicate journal entry
     RETURN redirect()->route('admin.sales-invoices.show', $existingTxn->sales_invoice_id)
       ->with('info', 'Payment has already been processed.');

5. // 2. Synchronously Call PayPal Capture API
   $token = $this->getAccessToken();
   $endpoint = $this->getBaseUrl() . "/v2/checkout/orders/{$orderId}/capture";

   $response = Http::withToken($token)
     ->withHeaders(['Content-Type' => 'application/json'])
     ->post($endpoint, []);

   IF $response->failed():
     THROW PaymentGatewayException("Capture failed: " . $response->body());

   $data = $response->json();
   $status = $data['status'] ?? 'FAILED';

   IF ($status !== 'COMPLETED'):
     THROW PaymentGatewayException("PayPal capture returned incomplete status: " . $status);

6. // 3. Extract Settled Amount and Capture ID
   $purchaseUnit = $data['purchase_units'][0] ?? [];
   $capture      = $purchaseUnit['payments']['captures'][0] ?? [];
   $captureId    = $capture['id'] ?? $orderId;
   $settledAmount= (float) ($capture['amount']['value'] ?? 0);
   $invoiceId    = (int) ($purchaseUnit['reference_id'] ? str_replace('INV-', '', $purchaseUnit['reference_id']) : 0);

7. // 4. Update PaymentTransaction record
   PaymentTransaction::updateOrCreate(
     ['external_reference' => $orderId],
     [
       'gateway'            => 'paypal',
       'sales_invoice_id'   => $invoiceId,
       'amount'             => $settledAmount,
       'currency'           => $capture['amount']['currency_code'] ?? 'DKK',
       'status'             => 'captured',
       'payload'            => $data
     ]
   );

8. // 5. Settle Invoice & Post GL Journal via Phase 5 Engine
   $paymentService = app(CustomerPaymentService::class);
   $payment = $paymentService->recordPayment([
     'sales_invoice_id'   => $invoiceId,
     'amount'             => $settledAmount,
     'payment_method'     => 'paypal',
     'reference_no'       => $captureId,
     'notes'              => "PayPal Direct Capture #{$captureId}"
   ], userId: auth()->id() ?? 1);

9. COMMIT DB transaction.
10. RETURN redirect()->route('admin.sales-invoices.show', $invoiceId)
      ->with('success', 'PayPal Payment of kr. ' . number_format($settledAmount, 2) . ' captured successfully!');
```

---

## 2. Cash On Delivery (COD) Driver & Cashier Protocol

Strict procedural separation ensures physical money maps accurately to ledger account `1010 (Petty Cash)`:

```text
STEP 1: ORDER ASSIGNMENT & DISPATCH
  - Sales Order flagged as payment_method = 'cod'
  - Delivery Challan / DO assigned to Driver ID
  - CodCollection created with status: 'out_for_delivery', expected_amount = $order->due_amount

STEP 2: FIELD CASH COLLECTION (Driver Handover)
  - Driver delivers goods and collects physical cash
  - Driver clicks "Mark Cash Collected" on Driver Portal
  - CodCollection status transitions to 'collected' (Goods released, cash in transit)

STEP 3: CASHIER HANDOVER & GL POSTING (Office Reconciliation)
  - Driver submits physical cash envelope to Company Cashier
  - Cashier enters CodCollection ID, counts cash, and verifies against expected amount:
    IF $physicalCash < $expectedAmount:
      $shortage = $expectedAmount - $physicalCash;
      Log Shortage / Create Delivery Discrepancy Note;
    
  - Cashier clicks "Confirm Handover & Deposit to Cash Drawer":
    1. CodCollection->update(['status' => 'handed_over', 'deposited_at' => now(), 'handed_over_to_user_id' => auth()->id()]);
    2. CustomerPaymentService::recordPayment([
         'order_id'       => $cod->order_id,
         'sales_invoice_id' => $cod->sales_invoice_id,
         'amount'         => $physicalCash,
         'payment_method' => 'cash',
         'account_id'     => 1010, // Head: Petty Cash / Cash in Hand
         'reference_no'   => 'COD-HANDOVER-' . $cod->id,
         'notes'          => "Physical COD cash verified by Cashier " . auth()->user()->name
       ]);
    3. Order payment_status becomes 'paid'.
```

---

## 3. High-Performance Feature Toggles Engine

Features must be evaluated thousands of times per hour without introducing database latency.

### 3.1 `is_feature_enabled` Evaluation Algorithm
```php
function is_feature_enabled(string $key, bool $default = true): bool
{
    static $requestCache = [];
    
    // 1. In-memory static cache for same-request speed (< 0.001ms)
    if (array_key_exists($key, $requestCache)) {
        return $requestCache[$key];
    }
    
    // 2. Application-level cache with 1-day TTL
    $toggles = Cache::remember('system_feature_toggles', 86400, function () {
        $settings = GeneralSetting::select('feature_toggles')->first();
        return $settings?->feature_toggles ?? [];
    });
    
    $isEnabled = isset($toggles[$key]) ? (bool) $toggles[$key] : $default;
    $requestCache[$key] = $isEnabled;
    
    return $isEnabled;
}
```

### 3.2 Dynamic Mutation & Cache Eviction
When Super Admin toggles a feature in the UI:
```php
public function updateToggle(Request $request)
{
    $key = $request->input('key');
    $value = (bool) $request->input('value');
    
    $setting = GeneralSetting::firstOrCreate(['id' => 1]);
    $toggles = $setting->feature_toggles ?? [];
    $toggles[$key] = $value;
    $setting->feature_toggles = $toggles;
    $setting->save();
    
    // Invalidate Cache
    Cache::forget('system_feature_toggles');
    
    return response()->json(['success' => true, 'key' => $key, 'status' => $value]);
}
```

---

## 4. Maintenance & Recovery Utilities (cPanel Safe)

### 4.1 Multi-Layered Cache Flushing Routine
```text
ROUTINE: ClearSystemCache()
  1. Artisan::call('cache:clear');     // Application Data Cache
  2. Artisan::call('route:clear');     // Compiled Route Cache
  3. Artisan::call('config:clear');    // Compiled Configuration Cache
  4. Artisan::call('view:clear');      // Compiled Blade Views
  5. IF function_exists('opcache_reset'):
       opcache_reset();                // PHP Zend OPcache
  6. Return benchmark: Memory cleared & execution duration in milliseconds.
```

### 4.2 Spatie Backup Trigger & Secure Download
```text
ROUTINE: GenerateSystemBackup(string $type = 'all')
  1. Verify Auth::user()->hasRole('Super Admin')
  2. Run Artisan::call('backup:run', ['--only-db' => ($type === 'db')])
  3. Record archive path into system_backup_logs
  4. Allow secure stream download: Storage::download($filePath)
```

### 4.3 Universal Soft-Delete Recycle Bin Scanner
```text
ROUTINE: ScanRecycleBin()
  1. Define Registered Models:
     [Product::class, User::class, Vendor::class, Order::class, SalesInvoice::class, PurchaseOrder::class, Quotation::class]
  2. FOREACH Model:
     $count = $model::onlyTrashed()->count();
     Return collection of trashed items with deletion timestamp and deleting user.
     
ROUTINE: SafeRestore($modelClass, $id)
  1. $record = $modelClass::onlyTrashed()->findOrFail($id);
  2. Validate unique constraints (e.g. check if SKU or email was re-used while item was trashed).
  3. $record->restore();
  
ROUTINE: GuardedForceDelete($modelClass, $id)
  1. $record = $modelClass::onlyTrashed()->findOrFail($id);
  2. Check for active foreign key relationships:
     - Check journal_entry_lines
     - Check stock_ledger / fifo_cost_layers
  3. IF referenced in financial or inventory records:
       THROW IntegrityViolationException("Cannot permanently delete record referenced in financial audit trail!");
  4. ELSE:
       $record->forceDelete();
```

---

## 5. Automated Verification & Stress Testing Procedures

To verify system integrity before client sign-off:

```bash
# 1. Zero-Imbalance General Ledger Invariant Check
php artisan test --filter=GeneralLedgerIntegrityTest

# 2. FIFO Valuation & Stock Depletion Consistency Test
php artisan test --filter=FifoValuationStressTest

# 3. PayPal Synchronous Capture Idempotency Test
php artisan test --filter=PayPalSynchronousCaptureTest

# 4. Feature Toggles Real-time Interception Test
php artisan test --filter=FeatureToggleInterceptionTest

# 5. Full Phase 6 Verification Suite
php artisan test tests/Feature/Phase6/
```
