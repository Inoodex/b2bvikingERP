# 🧠 Skill: Point of Sale (POS), Multi-Outlet Cashier & Shift Engine
**Category:** `07_pos_and_retail`  
**Standard:** Enterprise Execution Playbook  

---

## 1. Real-Time Barcode Scanning & Outlet Stock Verification Routine

When a barcode is scanned or an SKU is entered at the POS counter:

### Input & Validation Engine:
```text
INPUT: int $outletId, string $barcodeOrSku, float $quantity = 1.0

1. START Query:
   $product = Product::where('barcode', $barcodeOrSku)
      ->orWhere('sku', $barcodeOrSku)
      ->first();

   IF !$product:
      // Try searching inside ProductVariant
      $variant = ProductVariant::where('barcode', $barcodeOrSku)
         ->orWhere('sku', $barcodeOrSku)
         ->first();
      IF $variant:
         $product = $variant->product;
         $variantId = $variant->id;
      ELSE:
         THROW ProductNotFoundException("Barcode '$barcodeOrSku' does not match any catalog item.");
   ELSE:
      $variantId = null;

2. Verify Available Physical Stock for this specific Outlet:
   $localStock = InventoryStock::where('outlet_id', $outletId)
      ->where('product_id', $product->id)
      ->where('variant_id', $variantId)
      ->value('quantity') ?? 0;

   IF $localStock < $quantity:
      RETURN [
         'status' => 'warning',
         'warning_code' => 'LOW_STOCK',
         'available_qty' => $localStock,
         'message' => "Warning: Only {$localStock} units left in this outlet!"
      ];

3. Resolve Pricing:
   // POS retail sales to walk-in tourists ALWAYS resolve to retail price, NOT outlet_price
   $unitPrice = $variant ? ($variant->price ?: $product->price) : $product->price;

4. Calculate VAT (Danish standard 25% Moms):
   $vatRate = 0.25;
   $taxAmount = round(($unitPrice * $quantity) * ($vatRate / (1 + $vatRate)), 2);
   $subtotalExVat = ($unitPrice * $quantity) - $taxAmount;

5. RETURN LineItem Payload:
   [
      'product_id'   => $product->id,
      'variant_id'   => $variantId,
      'name'         => $product->name . ($variant ? " ({$variant->name})" : ""),
      'sku'          => $variant ? ($variant->sku ?: $product->sku) : $product->sku,
      'unit_price'   => (float)$unitPrice,
      'qty'          => $quantity,
      'subtotal'     => round($unitPrice * $quantity, 2),
      'tax_amount'   => $taxAmount,
      'local_stock'  => (float)$localStock,
   ];
```

---

## 2. Atomic FIFO Depletion & POS Sales Transaction Routine

When cashier clicks **"Complete Sale" (Cash / Card / MobilePay)**:

### Execution Algorithm:
```text
INPUT: int $outletId, int $shiftId, int $cashierId, array $items, string $paymentMethod, float $cashReceived

1. START DB::transaction()

2. Enforce Shift Active:
   $shift = PosShift::where('id', $shiftId)
      ->where('outlet_id', $outletId)
      ->where('status', 'open')
      ->lockForUpdate()
      ->firstOrFail();

3. Generate Sequential POS Sale Receipt Number:
   $saleNo = OrderNumberService::generate('POS', PosSale::class, 'pos_sales');

4. Calculate Totals:
   $subtotal = collect($items)->sum(fn($i) => (float)$i['qty'] * (float)$i['unit_price']);
   $taxAmount = round($subtotal * (0.25 / 1.25), 2); // Included 25% Danish VAT
   $totalAmount = $subtotal;
   $changeReturned = max(0, $cashReceived - $totalAmount);

5. Create PosSale Header:
   $sale = PosSale::create([
      'sale_no'         => $saleNo,
      'outlet_id'       => $outletId,
      'shift_id'        => $shift->id,
      'cashier_id'      => $cashierId,
      'subtotal'        => $subtotal,
      'tax_amount'      => $taxAmount,
      'total_amount'    => $totalAmount,
      'payment_method'  => $paymentMethod,
      'cash_received'   => $cashReceived,
      'change_returned' => $changeReturned,
      'status'          => 'completed'
   ]);

6. Deplete Inventory in Strict FIFO Order per Item:
   $fifoService = app(FifoDepletionService::class);
   $totalCOGS = 0;

   FOREACH item IN $items:
      $depletionResult = $fifoService->depleteStock(
         $outletId,
         null, // bin
         $item['product_id'],
         $item['variant_id'] ?? null,
         $item['qty'],
         'pos_sale',
         $sale->id
      );

      PosSaleItem::create([
         'pos_sale_id' => $sale->id,
         'product_id'  => $item['product_id'],
         'variant_id'  => $item['variant_id'] ?? null,
         'qty'         => $item['qty'],
         'unit_price'  => $item['unit_price'],
         'unit_cost'   => $depletionResult['avg_unit_cost'],
         'subtotal'    => round($item['qty'] * $item['unit_price'], 2),
         'tax'         => round(($item['qty'] * $item['unit_price']) * (0.25 / 1.25), 2),
         'total'       => round($item['qty'] * $item['unit_price'], 2)
      ]);

      $totalCOGS += ($depletionResult['avg_unit_cost'] * $item['qty']);

7. Update Active Shift Aggregates:
   $shift->increment('total_sales_amount', $totalAmount);
   IF $paymentMethod === 'cash':
      $shift->increment('total_cash_amount', ($totalAmount));
   ELSE IF $paymentMethod === 'card':
      $shift->increment('total_card_amount', $totalAmount);
   ELSE IF $paymentMethod === 'mobilepay':
      $shift->increment('total_mobile_amount', $totalAmount);

8. Dispatch Double-Entry General Ledger Posting:
   // Resolve Accounts:
   // Cash Account: 1010 (or outlet specific sub-account)
   // Sales Revenue: 4010
   // VAT Payable: 2020
   // COGS Expense: 5010
   // Inventory Asset: 1040 (or 1050)
   $journalService = app(JournalEntryService::class);
   $journalService->postJournal(
      'POS Retail Counter Sale',
      $sale,
      [
         ['account_code' => ($paymentMethod === 'cash' ? '1010' : '1020'), 'debit' => $totalAmount, 'credit' => 0],
         ['account_code' => '4010', 'debit' => 0, 'credit' => ($totalAmount - $taxAmount)],
         ['account_code' => '2020', 'debit' => 0, 'credit' => $taxAmount],
         ['account_code' => '5010', 'debit' => $totalCOGS, 'credit' => 0],
         ['account_code' => '1040', 'debit' => 0, 'credit' => $totalCOGS]
      ],
      now()->toDateString(),
      "POS Receipt #{$sale->sale_no} at Outlet #{$outletId}"
   );

9. COMMIT DB::transaction()
10. RETURN [
       'success' => true,
       'sale_id' => $sale->id,
       'sale_no' => $sale->sale_no,
       'receipt_html_url' => route('outlet.pos.receipt', $sale->id)
    ];
```

---

## 3. Shift Opening, Cash Drop & End-of-Day Z-Report Reconcile Routine

### 3.1 Shift Start Routine:
```text
INPUT: int $registerId, int $outletId, float $openingFloat
1. Verify no existing 'open' shift on this register.
2. PosShift::create([
      'register_id'   => $registerId,
      'outlet_id'     => $outletId,
      'user_id'       => Auth::id(),
      'opening_cash'  => $openingFloat,
      'status'        => 'open',
      'opened_at'     => now()
   ]);
```

### 3.2 Mid-Day Cash Drop (Safe Transfer):
```text
INPUT: PosShift $shift, float $dropAmount, string $note
1. Verify $dropAmount <= ($shift->opening_cash + $shift->total_cash_amount - $shift->total_drops);
2. Create PosCashDrop record (transfers excess cash to store safe).
3. Post Journal: DR 1011 (Store Safe Cash) / CR 1010 (Register Drawer Cash).
```

### 3.3 End-of-Day Z-Report Closing Routine:
```text
INPUT: PosShift $shift, float $physicalCountedCash, string $closingNotes

1. Calculate Expected Cash in Drawer:
   $expectedCash = ($shift->opening_cash + $shift->total_cash_amount) - $shift->total_drops;

2. Calculate Discrepancy:
   $discrepancy = $physicalCountedCash - $expectedCash; 
   // > 0 = Cash Overage; < 0 = Cash Shortage

3. Generate Official Z-Report Sequence:
   $zReportNo = OrderNumberService::generate('Z-REP', PosShift::class, 'pos_shifts');

4. Update Shift:
   $shift->update([
      'closing_cash_expected' => $expectedCash,
      'closing_cash_counted'  => $physicalCountedCash,
      'cash_discrepancy'      => $discrepancy,
      'status'                => 'closed',
      'z_report_no'           => $zReportNo,
      'closed_at'             => now(),
      'closing_notes'         => $closingNotes
   ]);

5. IF abs($discrepancy) > 0.01:
      // Post Cash Over/Short Journal
      IF $discrepancy < 0:
         // Cash Shortage: DR 5090 (Cash Shortage Expense) / CR 1010 (Cash in Hand)
         $journalService->postJournal('Cash Shortage', $shift, [
            ['account_code' => '5090', 'debit' => abs($discrepancy), 'credit' => 0],
            ['account_code' => '1010', 'debit' => 0, 'credit' => abs($discrepancy)]
         ]);
      ELSE:
         // Cash Overage: DR 1010 (Cash in Hand) / CR 4090 (Cash Overage Revenue)
         $journalService->postJournal('Cash Overage', $shift, [
            ['account_code' => '1010', 'debit' => $discrepancy, 'credit' => 0],
            ['account_code' => '4090', 'debit' => 0, 'credit' => $discrepancy]
         ]);

6. Format & Trigger 80mm Z-Report Slip Print.
```

---

## 4. Manager PIN Security Override Rulebook

To prevent cashier fraud and inventory desynchronization, the following actions **strictly lock** the POS terminal until an authorized Manager PIN is verified:

1. **Item Void / Line Removal**: If a cashier scans an item and later removes it from an active cart.
2. **Manual Price Override / Discount > 10%**: Cashiers cannot alter unit prices unless approved by the Store Manager.
3. **Product Return / Customer Refund**: Processing a refund and restocking items into inventory requires Manager authentication.
4. **Manual Cash Drawer Kick**: Popping the physical cash drawer open without an associated sale transaction.

### Verification Routine:
```text
INPUT: int $outletId, string $pinCode, string $actionType

1. $manager = User::role(['Outlet Manager', 'Admin', 'Manager'])
      ->where('outlet_id', $outletId)
      ->where('pos_manager_pin', hash('sha256', $pinCode))
      ->first();

2. IF !$manager:
      THROW SecurityException("Invalid Manager PIN. Action '$actionType' unauthorized.");

3. Log Audit Trail:
   AuditLog::create([
      'user_id'     => $manager->id,
      'action'      => "MANAGER_OVERRIDE_{$actionType}",
      'outlet_id'   => $outletId,
      'occurred_at' => now()
   ]);

4. RETURN true;
```
