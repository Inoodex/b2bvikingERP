# 🧠 Skill: Commercial Pricing, Credit Control & Delivery Engine
**Category:** `03_sales_and_fulfillment`  
**Standard:** Enterprise Execution Playbook

---

## 1. Dynamic Pricelist Resolver Algorithm (`PricelistResolverService.php`)

To determine the exact unit price for a customer and product variant:

```text
INPUT: User $customer, Product $product, ?ProductVariant $variant, float $quantity

1. Check if $customer has an assigned pricelist_id:
   $pricelist = $customer->pricelist ?? Pricelist::where('is_default', true)->first();

2. IF $pricelist:
   - Search pricelist_items where:
     - pricelist_id == $pricelist->id
     - product_id == $product->id
     - variant_id == $variant?->id
     - min_quantity <= $quantity
     ORDER BY min_quantity DESC, id DESC

   - IF matching item found:
     a) IF item.type == 'fixed': RETURN item.fixed_price
     b) IF item.type == 'multiplier': RETURN round($product->purchase_price * item.multiplier_value, 2)
     c) IF item.type == 'percentage_discount': RETURN round($product->price * (1 - item.discount_percent / 100), 2)

3. Fallback to Customer/Outlet Tier:
   - IF $customer->role == 'Outlet User' OR $customer->is_b2b_partner:
     RETURN $variant?->outlet_price ?? $product->outlet_price ?? $product->price
   - ELSE:
     RETURN $variant?->price ?? $product->price
```

---

## 2. B2B Customer Credit Limit & Aging Balance Validator

Prior to confirming a Sales Order:

```text
INPUT: User $customer, float $newOrderAmount

1. Fetch customer credit configuration:
   $creditLimit = (float) $customer->credit_limit;
   $creditDays = (int) ($customer->credit_days ?? 30);

2. IF $creditLimit <= 0:
   RETURN ['allowed' => true] // No credit restriction (Cash in advance)

3. Calculate current outstanding receivables:
   $currentDue = DB::table('sales_invoices')
     ->where('customer_id', $customer->id)
     ->whereIn('payment_status', ['unpaid', 'partial'])
     ->sum('due_amount');

4. Check Credit Limit Breach:
   IF ($currentDue + $newOrderAmount) > $creditLimit:
     RETURN [
       'allowed' => false,
       'reason' => "Credit limit exceeded. Limit: $creditLimit, Current Due: $currentDue, New Order: $newOrderAmount"
     ]

5. Check Overdue Invoices Breach (Aging Guard):
   $hasOverdue = DB::table('sales_invoices')
     ->where('customer_id', $customer->id)
     ->whereIn('payment_status', ['unpaid', 'partial'])
     ->where('created_at', '<', now()->subDays($creditDays))
     ->exists();

   IF $hasOverdue:
     RETURN [
       'allowed' => false,
       'reason' => "Customer has overdue unpaid invoices exceeding $creditDays days. Settlement required."
     ]

6. RETURN ['allowed' => true]
```

---

## 3. Delivery Order (DO) Stock Depletion Invariant Engine

When dispatching a `DeliveryOrder`:

```text
INPUT: DeliveryOrder $do

1. START DB::transaction()
2. Lock $do and related $do->items
3. FOREACH item IN $do->items:
   - Lock physical stock:
     $stock = InventoryStock::where('outlet_id', $do->outlet_id)
       ->where('product_id', $item->product_id)
       ->where('variant_id', $item->variant_id)
       ->lockForUpdate()
       ->first();

   - IF !$stock OR $stock->quantity < $item->dispatched_qty:
     THROW InsufficientStockException("Insufficient stock for product ID {$item->product_id} at outlet {$do->outlet_id}!");

   - $stock->decrement('quantity', $item->dispatched_qty);

   - Write immutable StockLedger entry:
     StockLedger::create([
       'outlet_id' => $do->outlet_id,
       'product_id' => $item->product_id,
       'variant_id' => $item->variant_id,
       'reference_type' => 'delivery_order',
       'reference_id' => $do->id,
       'in_qty' => 0,
       'out_qty' => $item->dispatched_qty,
       'balance_qty' => $stock->quantity,
       'date' => now()->toDateString()
     ]);

4. Update $do->status = 'dispatched', $do->dispatched_at = now()
5. COMMIT and RETURN true
```
