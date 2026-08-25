# 🧠 Skill: Enterprise WMS & Automated Logistics (V2)
**Category:** `04_inventory_and_wms`  
**Standard:** Enterprise Execution Playbook

---

## 1. Automated Barcode & QR Code Engine

When generating barcodes for physical goods or locations:

### Mathematical & Logical Steps:
```text
INPUT: Object $entity (Product, StockBatch, or WarehouseBin)

1. IF $entity is WarehouseBin:
   Generate Barcode: 'BIN-' . $outlet_id . '-' . $zone_id . '-' . $bin_id
   Example: BIN-1-2-15

2. IF $entity is StockBatch (Incoming GRN):
   Generate QR Payload:
   {
      "batch_no": "BCH-20260825-001",
      "product_id": 105,
      "landed_cost": 450.50,
      "expiry": "2027-12-31"
   }
   Convert to base64 or unique hash: $barcode = generateUniqueHash()
   Save $barcode to $stockBatch->barcode.

3. TRIGGER PDF Generation:
   Load view 'pdf.barcode_sticker' -> Print via connected thermal printer API.
```

---

## 2. Micro-Location (Bin) Depletion Algorithm

When a delivery order is scanned, it must consume stock from a specific bin.

### Mathematical & Logical Steps:
```text
INPUT: int $outletId, int $binId, int $productId, float $requiredQty

1. START DB::transaction()
2. Verify total available physical stock IN SPECIFIC BIN:
   $stock = InventoryStock::where('outlet_id', $outletId)
     ->where('bin_id', $binId)
     ->where('product_id', $productId)
     ->lockForUpdate()
     ->first();

   IF !$stock OR $stock->quantity < $requiredQty:
     THROW InsufficientStockException("Bin lacks required quantity!");

3. Fetch active batches in FIFO order:
   $batches = DB::table('stock_batches')
     ->where('outlet_id', $outletId)
     ->where('product_id', $productId)
     ->where('remaining_qty', '>', 0)
     ->where('status', 'active')
     ->orderBy('received_date', 'asc')
     ->lockForUpdate()
     ->get();

4. Initialize variables & Loop (Same as standard FIFO depletion):
   $remainingToDeplete = $requiredQty;
   $totalCOGS = 0;
   
   FOREACH batch IN $batches:
     IF $remainingToDeplete <= 0: BREAK;
     $qtyToTake = min($batch->remaining_qty, $remainingToDeplete);
     // Update batch remaining_qty
     // Add to $totalCOGS

5. Decrement physical InventoryStock:
   $stock->decrement('quantity', $requiredQty);

6. Write immutable StockLedger entry:
   StockLedger::create([
     'outlet_id' => $outletId,
     'product_id' => $productId,
     'in_qty' => 0,
     'out_qty' => $requiredQty,
     'balance_qty' => InventoryStock::where('product_id', $productId)->sum('quantity'), // Total outlet balance
     'unit_cost' => ($totalCOGS / $requiredQty),
     'date' => now()
   ]);

7. COMMIT
```

---

## 3. Auto-Replenishment Cron Engine

Running nightly to ensure no stockouts occur.

### Mathematical & Logical Steps:
```text
SCHEDULE: Daily at 01:00 AM

1. FETCH all products with min_order_qty > 0.
2. FOREACH $product:
   $totalStock = InventoryStock::where('product_id', $product->id)->sum('quantity');
   
   IF $totalStock <= $product->minimum_order_qty:
     
     // Step A: Find Primary Vendor from product_vendors
     $vendor = DB::table('product_vendors')
                 ->where('product_id', $product->id)
                 ->orderBy('purchase_price', 'asc')
                 ->first();
                 
     IF $vendor:
        // Step B: Auto-generate Draft Purchase Order
        $draftPo = Purchase::create([
            'vendor_id' => $vendor->vendor_id,
            'status' => 'draft',
            'type' => 'auto_replenishment',
            'total_amount' => $vendor->purchase_price * $product->minimum_order_qty
        ]);
        
        $draftPo->details()->create([
            'product_id' => $product->id,
            'qty' => $product->minimum_order_qty,
            'unit_cost' => $vendor->purchase_price
        ]);
        
        // Step C: Send Notification to Procurement Manager
        NotifyManager("Auto-Replenishment Draft PO #{$draftPo->id} created for Product {$product->name}");
```
