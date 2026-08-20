# 🧠 Skill: Landed Cost Allocation & Procurement Engine
**Category:** `02_procurement`  
**Standard:** Enterprise Execution Playbook

---

## 1. Weighted Average Landed Cost Allocation Algorithm

When imported goods arrive under a Letter of Credit (LC) with shared overheads (Customs Duty, Freight, Insurance, C&F Agent fee, Transport, Port Handling):

### Mathematical Formulation:
Let:
* $E_{total} = \sum (\text{CD} + \text{RD} + \text{SD} + \text{VAT} + \text{AIT} + \text{Freight} + \text{Insurance} + \text{C\&F} + \dots)$
* $V_{total} = \sum (\text{item.qty} \times \text{item.unit\_cost\_converted})$

For each item $i$:
$$\text{Allocation Ratio}_i = \frac{V_i}{V_{total}}$$
$$\text{Allocated Overhead}_i = E_{total} \times \text{Allocation Ratio}_i$$
$$\text{Unit Landed Cost}_i = \text{Converted Base Unit Cost}_i + \left(\frac{\text{Allocated Overhead}_i}{\text{item.qty}_i}\right)$$

### Service Implementation Workflow (`LandedCostService.php`):
```text
INPUT: Purchase $purchase

1. START DB::transaction()
2. Lock $purchase and its lc_expenses
3. Fetch all approved LC expenses: $totalOverhead = $purchase->lcExpenses()->sum('amount')
4. Calculate total base value in system currency: $totalBaseValue = 0
   FOREACH item IN $purchase->items:
     item.base_converted = item.unit_cost_vendor * purchase.exchange_rate
     totalBaseValue += (item.base_converted * item.qty)

5. IF totalBaseValue > 0:
   FOREACH item IN $purchase->items:
     item.value_ratio = (item.base_converted * item.qty) / totalBaseValue
     item.overhead_share = totalOverhead * item.value_ratio
     item.landed_cost = item.base_converted + (item.overhead_share / item.qty)
     item.save()

6. COMMIT and RETURN true
```

---

## 2. Over-Receipt Guard Algorithm (`GoodsReceiptController.php`)

To prevent inventory bloat and accounting discrepancies, every GRN submission is strictly validated against prior receipts:

```text
INPUT: int $purchaseId, array $submittedItems (product_id, variant_id, accepted_qty, rejected_qty)

FOREACH item IN $submittedItems:
  1. Fetch total previously accepted across all non-failed GRNs:
     $previouslyAccepted = DB::table('goods_receipt_items')
       ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
       ->where('goods_receipts.purchase_id', $purchaseId)
       ->where('goods_receipts.qc_status', '!=', 'failed')
       ->where('goods_receipt_items.product_id', item.product_id)
       ->where('goods_receipt_items.variant_id', item.variant_id)
       ->sum('accepted_qty');

  2. Fetch PO ordered quantity: $orderedQty = $poDetail->qty;
  3. $remainingEligible = max(0, $orderedQty - $previouslyAccepted);

  4. IF item.accepted_qty > ($remainingEligible + 0.0001):
     THROW OverReceiptException("Accepted quantity exceeds remaining ordered quantity ($remainingEligible)!");
```

---

## 3. 3-Way Matching Engine (PO vs GRN vs Vendor Bill)

When creating a `VendorBill`:
* **Line-Item Price:** Must match the negotiated `PurchaseOrder` unit cost.
* **Billed Quantity:** Must be $\le$ the `GoodsReceipt` accepted quantity.
* **Variance Threshold:** If difference $> 0$, flag as price variance requiring Accounts Manager approval.
