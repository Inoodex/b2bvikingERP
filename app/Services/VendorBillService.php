<?php

namespace App\Services;

use App\Models\DebitNoteSettlement;
use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Models\VendorBill;
use App\Models\VendorReturn;
use App\Services\Accounting\JournalEntryService;
use Exception;
use Illuminate\Support\Facades\DB;

class VendorBillService
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Create a new Vendor Bill from a Goods Receipt or Purchase Order.
     */
    public function createBill(array $data): VendorBill
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::with(['details', 'goodsReceipts'])->findOrFail($data['purchase_id']);
            $goodsReceipt = isset($data['goods_receipt_id']) && $data['goods_receipt_id']
                ? GoodsReceipt::with('items')->find($data['goods_receipt_id'])
                : null;

            // Generate unique sequential Bill Number: BILL-YYYYMMDD-XXXXX
            $today = now()->format('Ymd');
            $countToday = VendorBill::whereDate('created_at', now()->toDateString())->count() + 1;
            $billNo = 'BILL-' . $today . '-' . str_pad($countToday, 5, '0', STR_PAD_LEFT);

            // Calculate item subtotal
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($data['items'] as $itemData) {
                $qty = (float) $itemData['qty'];
                $unitPrice = (float) $itemData['unit_price'];
                $landedCost = isset($itemData['landed_cost']) ? (float) $itemData['landed_cost'] : $unitPrice;
                $effectivePrice = $landedCost > 0 ? $landedCost : $unitPrice;
                $lineTotal = round($qty * $effectivePrice, 2);

                $subtotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_id'   => $itemData['product_id'],
                    'variant_id'   => $itemData['variant_id'] ?? null,
                    'description'  => $itemData['description'] ?? null,
                    'qty'          => $qty,
                    'unit_price'   => $unitPrice,
                    'landed_cost'  => $landedCost,
                    'line_total'   => $lineTotal,
                ];
            }

            // Check for unapplied Debit Notes on this PO / Vendor Return
            $debitNoteAdjustment = 0;
            $debitNotesToSettle = [];

            if (!isset($data['apply_debit_notes']) || $data['apply_debit_notes']) {
                $pendingReturns = VendorReturn::with('items')
                    ->where('purchase_id', $purchase->id)
                    ->where('status', 'approved')
                    ->get();

                foreach ($pendingReturns as $return) {
                    $alreadySettled = DebitNoteSettlement::where('vendor_return_id', $return->id)->sum('settled_amount');
                    $unsettled = max(0, (float)$return->total_claim_amount - (float)$alreadySettled);

                    if ($unsettled > 0) {
                        $debitNoteAdjustment += $unsettled;
                        $debitNotesToSettle[] = [
                            'return' => $return,
                            'amount' => $unsettled,
                        ];
                    }
                }
            }

            $taxAmount = isset($data['tax_amount']) ? (float) $data['tax_amount'] : 0;
            $discountAmount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0;
            $grandTotal = max(0, round($subtotal + $taxAmount - $discountAmount - $debitNoteAdjustment, 2));

            $bill = VendorBill::create([
                'bill_no'               => $billNo,
                'purchase_id'           => $purchase->id,
                'vendor_id'             => $purchase->vendor_id,
                'goods_receipt_id'      => $goodsReceipt?->id,
                'currency_id'           => $purchase->currency_id,
                'bill_date'             => $data['bill_date'],
                'due_date'              => $data['due_date'] ?? now()->addDays(30)->toDateString(),
                'subtotal'              => $subtotal,
                'tax_amount'            => $taxAmount,
                'discount_amount'       => $discountAmount,
                'debit_note_adjustment' => $debitNoteAdjustment,
                'grand_total'           => $grandTotal,
                'paid_amount'           => 0,
                'due_amount'            => $grandTotal,
                'payment_status'        => $grandTotal == 0 ? 'paid' : 'unpaid',
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => auth()->id() ?? 1,
            ]);

            // Save bill items
            foreach ($itemsToCreate as $item) {
                $bill->items()->create($item);
            }

            // Create Debit Note Settlement records
            foreach ($debitNotesToSettle as $dn) {
                DebitNoteSettlement::create([
                    'vendor_return_id' => $dn['return']->id,
                    'vendor_bill_id'   => $bill->id,
                    'settled_amount'   => $dn['amount'],
                    'settlement_date'  => now(),
                    'notes'            => 'Auto-applied against Vendor Bill ' . $billNo,
                    'settled_by'       => auth()->id() ?? 1,
                ]);
            }

            // Post General Ledger Double-Entry Journal: DR Inventory (1050) / CR Accounts Payable (2010)
            if ($grandTotal > 0) {
                $lines = [
                    ['account_code' => '1050', 'debit' => $grandTotal, 'credit' => 0], // DR Inventory Asset
                    ['account_code' => '2010', 'debit' => 0,          'credit' => $grandTotal], // CR Accounts Payable
                ];

                $vendorName = $purchase->vendor ? $purchase->vendor->shop_name : 'Supplier';
                $billDate = $bill->bill_date ? date('Y-m-d', strtotime($bill->bill_date)) : now()->toDateString();
                $this->journalService->postJournal(
                    'Vendor Bill Created',
                    $bill,
                    $lines,
                    $billDate,
                    "Vendor Bill #{$bill->bill_no} ({$vendorName}) for PO #{$purchase->po_no}"
                );
            }

            return $bill;
        });
    }
}
