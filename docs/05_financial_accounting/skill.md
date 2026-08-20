# 🧠 Skill: Double-Entry Bookkeeping & Journal Posting Engine
**Category:** `05_financial_accounting`  
**Standard:** Enterprise Execution Playbook

---

## 1. Automated Double-Entry Journal Observer Rulebook

Every automated operational transaction dispatches an event to the `JournalEntryService`:

### Mathematical Balanced Posting Engine:
```text
INPUT: string $event, Model $sourceModel, array $lines (account_code, debit, credit, description, party_id)

1. START DB::transaction()
2. Validate Zero-Imbalance:
   $totalDebit = collect($lines)->sum('debit');
   $totalCredit = collect($lines)->sum('credit');

   IF abs($totalDebit - $totalCredit) > 0.001:
     THROW JournalImbalanceException("Debit ($totalDebit) does not match Credit ($totalCredit)!");

3. Generate unique sequential entry number:
   $entryNo = OrderNumberService::generate('JV', 'journal_entries', 'entry_no');

4. Create JournalEntry header:
   $entry = JournalEntry::create([
     'entry_no' => $entryNo,
     'date' => $sourceModel->date ?? now()->toDateString(),
     'reference_type' => $sourceModel->getMorphClass(),
     'reference_id' => $sourceModel->id,
     'description' => "Automated journal for {$event} #{$sourceModel->id}",
     'status' => 'posted'
   ]);

5. FOREACH line IN $lines:
   - Resolve ChartOfAccount by code: $account = ChartOfAccount::where('code', $line['account_code'])->firstOrFail();
   
   - Create JournalEntryLine:
     JournalEntryLine::create([
       'journal_entry_id' => $entry->id,
       'account_id' => $account->id,
       'debit_amount' => $line['debit'],
       'credit_amount' => $line['credit'],
       'description' => $line['description'] ?? $entry->description,
       'party_type' => $line['party_type'] ?? null,
       'party_id' => $line['party_id'] ?? null
     ]);

6. Update account running balance caches.
7. COMMIT and RETURN $entry
```

---

## 2. Straight-Line Fixed Asset Depreciation Calculator

For monthly automated depreciation schedules:

### Formula:
$$\text{Monthly Depreciation} = \frac{\text{Purchase Cost} - \text{Salvage Value}}{\text{Useful Life in Years} \times 12}$$

### Calculation Routine:
```text
INPUT: Asset $asset

1. $depreciableBasis = max(0, $asset->purchase_cost - $asset->salvage_value);
2. $totalMonths = max(1, $asset->useful_life_years * 12);
3. $monthlyAmount = round($depreciableBasis / $totalMonths, 2);

4. IF ($asset->current_value - $monthlyAmount) < $asset->salvage_value:
     $monthlyAmount = max(0, $asset->current_value - $asset->salvage_value);

5. IF $monthlyAmount > 0:
     - Post Journal: DR 5080 (Depreciation Expense) / CR 1090 (Accumulated Depreciation)
     - $asset->decrement('current_value', $monthlyAmount);
     - Log AssetDepreciation record.
```

---

## 3. Bank Statement Auto-Matching Reconciliation Rules

When ingesting bank statement CSV/API transactions:
1. **Rule 1 (Exact Reference Match):** If `statement.reference` matches an open `CustomerPayment.transaction_id` or `PurchasePayment.cheque_no` $\rightarrow$ Auto-Match (Confidence: 100%).
2. **Rule 2 (Exact Amount & Date $\pm$ 2 days):** If amount matches exactly within a 48-hour window for the same vendor/customer $\rightarrow$ Propose Match (Confidence: 95%).
3. **Rule 3 (Manual Reconcile):** User selects multiple invoices/payments to balance a single bank deposit/withdrawal.
