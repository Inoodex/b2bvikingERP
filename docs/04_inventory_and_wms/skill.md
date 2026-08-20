# 🧠 Skill: FIFO Stock Depletion & Inventory Algorithms
**Category:** `04_inventory_and_wms`  
**Standard:** Enterprise Execution Playbook

---

## 1. FIFO (First-In, First-Out) Depletion Algorithm

When a delivery order, sales order, or stock issue consumes physical inventory:

### Mathematical & Logical Steps:
```text
INPUT: int $outletId, int $productId, ?int $variantId, float $requiredQty

1. START DB::transaction()
2. Verify total available physical stock:
   $stock = InventoryStock::where('outlet_id', $outletId)
     ->where('product_id', $productId)
     ->where('variant_id', $variantId)
     ->lockForUpdate()
     ->first();

   IF !$stock OR $stock->quantity < $requiredQty:
     THROW InsufficientStockException("Required quantity ($requiredQty) exceeds available stock!");

3. Fetch active batches in strict FIFO order (Oldest first):
   $batches = DB::table('stock_batches')
     ->where('outlet_id', $outletId)
     ->where('product_id', $productId)
     ->where('variant_id', $variantId)
     ->where('remaining_qty', '>', 0)
     ->where('status', 'active')
     ->orderBy('received_date', 'asc')
     ->orderBy('id', 'asc')
     ->lockForUpdate()
     ->get();

4. Initialize variables:
   $remainingToDeplete = $requiredQty;
   $depletionLog = [];
   $totalCOGS = 0;

5. FOREACH batch IN $batches:
   IF $remainingToDeplete <= 0: BREAK;

   $qtyToTake = min($batch->remaining_qty, $remainingToDeplete);
   $newRemaining = $batch->remaining_qty - $qtyToTake;
   $newStatus = ($newRemaining <= 0) ? 'depleted' : 'active';

   // Update Batch
   DB::table('stock_batches')->where('id', $batch->id)->update([
     'remaining_qty' => $newRemaining,
     'status' => $newStatus,
     'updated_at' => now()
   ]);

   // Calculate COGS
   $cogsContribution = $qtyToTake * $batch->unit_landed_cost;
   $totalCOGS += $cogsContribution;

   $depletionLog[] = [
     'batch_id' => $batch->id,
     'batch_no' => $batch->batch_no,
     'qty_depleted' => $qtyToTake,
     'unit_landed_cost' => $batch->unit_landed_cost,
     'cogs_amount' => $cogsContribution
   ];

   $remainingToDeplete -= $qtyToTake;

6. IF $remainingToDeplete > 0:
   // Fallback for legacy stock prior to batch engine activation:
   $fallbackUnitCost = Product::find($productId)?->purchase_price ?? 0;
   $totalCOGS += ($remainingToDeplete * $fallbackUnitCost);

7. Decrement physical InventoryStock:
   $stock->decrement('quantity', $requiredQty);

8. Write immutable StockLedger entry:
   StockLedger::create([
     'outlet_id' => $outletId,
     'product_id' => $productId,
     'variant_id' => $variantId,
     'reference_type' => 'delivery_order',
     'reference_id' => $referenceId,
     'in_qty' => 0,
     'out_qty' => $requiredQty,
     'balance_qty' => $stock->quantity,
     'unit_cost' => ($requiredQty > 0) ? ($totalCOGS / $requiredQty) : 0,
     'date' => now()->toDateString()
   ]);

9. COMMIT and RETURN [
     'depletion_log' => $depletionLog,
     'total_cogs' => $totalCOGS,
     'average_depleted_cost' => ($requiredQty > 0) ? ($totalCOGS / $requiredQty) : 0
   ];
```

---

## 2. 3-Stage Stock Transfer Invariant Engine

To move goods between outlets with 100% audit integrity:

```text
STAGE 1: Draft / Request
- Source Outlet creates Transfer.
- Status: 'draft' -> 'pending_approval' (No physical stock changed).

STAGE 2: Dispatch (In-Transit)
- Manager approves.
- Source warehouse clicks 'Dispatch':
  a) Decrement source outlet InventoryStock: $sourceStock->decrement('quantity', $qty);
  b) Log StockLedger (reference_type: 'transfer_out', out_qty: $qty).
  c) Generate PDF Delivery Challan / Transfer Waybill.
  d) Status transitions to 'in_transit'.

STAGE 3: Receive (Destination Outlet)
- Destination warehouse inspects goods and clicks 'Receive':
  a) Increment destination outlet InventoryStock: $destStock->increment('quantity', $receivedQty);
  b) Log StockLedger (reference_type: 'transfer_in', in_qty: $receivedQty).
  c) Replicate stock_batch to destination outlet with original unit_landed_cost.
  d) IF $receivedQty < $dispatchedQty:
     - Log variance / damage adjustment for difference.
  e) Status transitions to 'received'.
```
