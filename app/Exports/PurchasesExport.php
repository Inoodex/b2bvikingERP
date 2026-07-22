<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        set_time_limit(300);
        return Purchase::with(['vendor', 'user', 'details'])
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'Vendor',
            'Products',
            'Created By',
            'Date',
            'Total Amount',
            'Paid Amount',
            'Due Amount',
            'Shipping',
            'Status',
            'Payment Status',
        ];
    }

    public function map($purchase): array
    {
        $items = [];
        foreach ($purchase->details as $detail) {
            $name = $detail->product?->name ?? 'Unknown';
            if ($detail->variant_info) {
                $variants = [];
                foreach ($detail->variant_info as $vName => $vQty) {
                    $variants[] = $vName . ': ' . $vQty;
                }
                $name .= ' (' . implode(', ', $variants) . ')';
            }
            $items[] = $name;
        }
        return [
            $purchase->invoice_no,
            $purchase->vendor?->shop_name ?? 'N/A',
            implode('; ', $items),
            $purchase->user?->name ?? 'System',
            $purchase->date,
            number_format($purchase->total_amount, 2),
            number_format($purchase->paid_amount, 2),
            number_format($purchase->due_amount, 2),
            $purchase->shipping_method ?? 'N/A',
            $purchase->status == 1 ? 'Completed' : 'Draft',
            ucfirst($purchase->payment_status ?? 'Pending'),
        ];
    }
}
