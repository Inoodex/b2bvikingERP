<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\StationeryConsumption;
use App\Models\StationeryConsumptionItem;
use App\Models\StationeryItem;
use App\Models\StationeryPurchase;
use App\Models\StationeryPurchaseItem;
use App\Models\StationeryStockLedger;
use App\Services\Accounting\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StationeryInventoryService
{
    /**
     * Record a new purchase and update physical stock with Weighted Average Cost (WAC).
     *
     * @param StationeryPurchase $purchase
     * @param array $items Array of ['stationery_item_id', 'quantity', 'unit_price']
     * @return StationeryPurchase
     */
    public function recordPurchaseInward(StationeryPurchase $purchase, array $items): StationeryPurchase
    {
        return DB::transaction(function () use ($purchase, $items) {
            $totalAmount = 0.0;

            foreach ($items as $row) {
                $itemId = $row['stationery_item_id'];
                $qty = (float) ($row['quantity'] ?? 0);
                $unitPrice = (float) ($row['unit_price'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $item = StationeryItem::lockForUpdate()->findOrFail($itemId);

                $oldStock = (float) $item->current_stock;
                $oldCost = (float) $item->unit_cost;
                $subtotal = round($qty * $unitPrice, 2);
                $totalAmount += $subtotal;

                // Weighted Average Cost (WAC) formula
                $newStock = $oldStock + $qty;
                if ($newStock > 0) {
                    $newUnitCost = (($oldStock * $oldCost) + ($qty * $unitPrice)) / $newStock;
                } else {
                    $newUnitCost = $unitPrice;
                }
                $newUnitCost = round($newUnitCost, 2);

                // Update Item Master
                $item->current_stock = $newStock;
                $item->unit_cost = $newUnitCost;
                $item->save();

                // Save Purchase Line
                StationeryPurchaseItem::create([
                    'stationery_purchase_id' => $purchase->id,
                    'stationery_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Record Audit Ledger
                StationeryStockLedger::create([
                    'stationery_item_id' => $item->id,
                    'transaction_type' => 'purchase_in',
                    'reference_id' => $purchase->id,
                    'reference_no' => $purchase->purchase_no,
                    'date' => $purchase->purchase_date,
                    'stock_before' => $oldStock,
                    'qty_in' => $qty,
                    'qty_out' => 0.00,
                    'stock_after' => $newStock,
                    'unit_cost' => $newUnitCost,
                    'total_value' => $subtotal,
                    'created_by' => $purchase->created_by,
                    'notes' => 'Stock In from: ' . $purchase->display_vendor,
                ]);
            }

            $purchase->total_amount = $totalAmount;
            if ($purchase->paid_amount <= 0 && $purchase->payment_status === 'paid') {
                $purchase->paid_amount = $totalAmount;
            }
            $purchase->save();

            // Post General Ledger Journal for Purchase Inward
            if ($totalAmount > 0) {
                $this->postPurchaseJournal($purchase);
            }

            return $purchase;
        });
    }

    /**
     * Record a department consumption issue, deduct physical stock, and log operational expense.
     *
     * @param StationeryConsumption $consumption
     * @param array $items Array of ['stationery_item_id', 'quantity']
     * @return StationeryConsumption
     * @throws InvalidArgumentException
     */
    public function recordConsumptionIssue(StationeryConsumption $consumption, array $items): StationeryConsumption
    {
        return DB::transaction(function () use ($consumption, $items) {
            $totalCost = 0.0;

            foreach ($items as $row) {
                $itemId = $row['stationery_item_id'];
                $qty = (float) ($row['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $item = StationeryItem::lockForUpdate()->findOrFail($itemId);
                $currentStock = (float) $item->current_stock;

                // Strict Safety Check: Prevent Negative Stock
                if ($qty > $currentStock) {
                    throw new InvalidArgumentException(
                        "Insufficient stock for [{$item->name}] ({$item->item_code}). Current stock is {$currentStock} {$item->unit_name}, but requested {$qty}."
                    );
                }

                $unitCost = (float) $item->unit_cost;
                $subtotal = round($qty * $unitCost, 2);
                $totalCost += $subtotal;
                $newStock = $currentStock - $qty;

                // Update physical stock
                $item->current_stock = $newStock;
                $item->save();

                // Save Consumption Line
                StationeryConsumptionItem::create([
                    'stationery_consumption_id' => $consumption->id,
                    'stationery_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Record Audit Ledger
                StationeryStockLedger::create([
                    'stationery_item_id' => $item->id,
                    'transaction_type' => 'consumption_out',
                    'reference_id' => $consumption->id,
                    'reference_no' => $consumption->issue_no,
                    'date' => $consumption->issue_date,
                    'stock_before' => $currentStock,
                    'qty_in' => 0.00,
                    'qty_out' => $qty,
                    'stock_after' => $newStock,
                    'unit_cost' => $unitCost,
                    'total_value' => $subtotal,
                    'created_by' => $consumption->created_by,
                    'notes' => 'Issued to ' . $consumption->display_department . ($consumption->issued_to ? ' (' . $consumption->issued_to . ')' : ''),
                ]);
            }

            $consumption->total_cost = $totalCost;
            $consumption->save();

            // Post automated double-entry Journal Voucher to General Ledger & Profit & Loss
            if ($totalCost > 0) {
                $this->postConsumptionJournal($consumption);
            }

            return $consumption;
        });
    }

    /**
     * Post balanced journal entry for stationery purchase inward:
     * DR [1050] Inventory in Warehouse (Asset)
     * CR [1010] Cash on Hand (if paid) OR [2010] Accounts Payable (Vendors)
     */
    public function postPurchaseJournal(StationeryPurchase $purchase): ?JournalEntry
    {
        $amount = (float) $purchase->total_amount;
        if ($amount <= 0) {
            return null;
        }

        // Avoid duplicate journal posting
        $alreadyPosted = JournalEntry::where('reference_type', $purchase->getMorphClass())
            ->where('reference_id', $purchase->id)
            ->first();

        if ($alreadyPosted) {
            return $alreadyPosted;
        }

        // If paid, credit the selected cash/bank account; if unpaid, credit Accounts Payable [2010]
        $creditAccount = ($purchase->payment_status === 'paid')
            ? ($purchase->payment_account_code ?: '1010')
            : '2010';

        $journalService = app(JournalEntryService::class);
        $purchaseDate = $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : now()->toDateString();

        return $journalService->postJournal(
            event: 'stationery_purchase',
            sourceModel: $purchase,
            lines: [
                ['account_code' => '1050', 'debit' => $amount, 'credit' => 0],
                ['account_code' => $creditAccount, 'debit' => 0, 'credit' => $amount],
            ],
            date: $purchaseDate,
            narration: "Stationery & Supplies Inward PO #{$purchase->purchase_no} from {$purchase->display_vendor}"
        );
    }

    /**
     * Settle an unpaid/due stationery purchase voucher with a company cash or bank account.
     */
    public function settleDuePayment(StationeryPurchase $purchase, string $paidAccountCode, ?string $paymentDate = null, ?string $notes = null): JournalEntry
    {
        if ($purchase->payment_status === 'paid') {
            throw new \Exception("Purchase PO #{$purchase->purchase_no} is already marked as paid in full.");
        }

        $amount = (float) $purchase->total_amount;
        $journalService = app(JournalEntryService::class);
        $settleDate = $paymentDate ? date('Y-m-d', strtotime($paymentDate)) : now()->toDateString();

        return DB::transaction(function () use ($purchase, $paidAccountCode, $settleDate, $amount, $journalService, $notes) {
            // DR Accounts Payable [2010] (Clears company liability)
            // CR Selected Bank/Cash Account (Money leaves company account)
            $entry = $journalService->postJournal(
                event: 'stationery_due_settled',
                sourceModel: $purchase,
                lines: [
                    ['account_code' => '2010', 'debit' => $amount, 'credit' => 0],
                    ['account_code' => $paidAccountCode, 'debit' => 0, 'credit' => $amount],
                ],
                date: $settleDate,
                narration: "Due Settlement for Stationery PO #{$purchase->purchase_no} ({$purchase->display_vendor})" . ($notes ? " - {$notes}" : "")
            );

            $purchase->payment_status = 'paid';
            $purchase->paid_amount = $amount;
            $purchase->payment_account_code = $paidAccountCode;
            $purchase->save();

            return $entry;
        });
    }

    /**
     * Post balanced journal entry for stationery department consumption:
     * DR [5025] Packaging & Stationery Supplies Expense (Operating Expense -> Profit & Loss)
     * CR [1050] Inventory in Warehouse (Asset reduction)
     */
    public function postConsumptionJournal(StationeryConsumption $consumption): ?JournalEntry
    {
        $cost = (float) $consumption->total_cost;
        if ($cost <= 0) {
            return null;
        }

        // Avoid duplicate journal posting
        $alreadyPosted = JournalEntry::where('reference_type', $consumption->getMorphClass())
            ->where('reference_id', $consumption->id)
            ->first();

        if ($alreadyPosted) {
            return $alreadyPosted;
        }

        $journalService = app(JournalEntryService::class);
        $issueDate = $consumption->issue_date ? $consumption->issue_date->format('Y-m-d') : now()->toDateString();

        return $journalService->postJournal(
            event: 'stationery_consumption',
            sourceModel: $consumption,
            lines: [
                ['account_code' => '5025', 'debit' => $cost, 'credit' => 0],
                ['account_code' => '1050', 'debit' => 0, 'credit' => $cost],
            ],
            date: $issueDate,
            narration: "Stationery & Supplies Consumption Voucher #{$consumption->issue_no} issued to {$consumption->display_department}" . ($consumption->issued_to ? " ({$consumption->issued_to})" : "")
        );
    }

    /**
     * Retroactively sync any existing purchases and consumptions to the General Ledger.
     */
    public function syncPendingJournals(): array
    {
        $purchasesPosted = 0;
        $consumptionsPosted = 0;

        foreach (StationeryPurchase::all() as $purchase) {
            if ($this->postPurchaseJournal($purchase)) {
                $purchasesPosted++;
            }
        }

        foreach (StationeryConsumption::all() as $consumption) {
            if ($this->postConsumptionJournal($consumption)) {
                $consumptionsPosted++;
            }
        }

        return [
            'purchases_posted' => $purchasesPosted,
            'consumptions_posted' => $consumptionsPosted,
        ];
    }

    /**
     * Compute real-time dashboard KPIs for stationery management.
     */
    public function getDashboardKpis(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        // High Performance: Pure SQL aggregates (Zero memory bloat, instant response even on 1M+ items)
        $totalItems = StationeryItem::where('status', true)->count();

        $valuationRaw = StationeryItem::where('status', true)
            ->selectRaw('COALESCE(SUM(current_stock * unit_cost), 0) as total_val')
            ->value('total_val');
        $totalValuation = (float) ($valuationRaw ?? 0.0);

        $outOfStockCount = StationeryItem::where('status', true)
            ->where('current_stock', '<=', 0)
            ->count();

        $lowStockCount = StationeryItem::where('status', true)
            ->whereColumn('current_stock', '<=', 'min_alert_qty')
            ->count();

        // Monthly consumption expense
        $monthExpense = (float) StationeryConsumption::whereBetween('issue_date', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');

        // Department breakdown this month
        $deptExpenses = StationeryConsumption::whereBetween('issue_date', [$startOfMonth, $endOfMonth])
            ->select('department_name', DB::raw('SUM(total_cost) as total'))
            ->groupBy('department_name')
            ->orderByDesc('total')
            ->get();

        return [
            'total_items' => $totalItems,
            'total_valuation' => round($totalValuation, 2),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'month_expense' => round($monthExpense, 2),
            'dept_expenses' => $deptExpenses,
        ];
    }
}

