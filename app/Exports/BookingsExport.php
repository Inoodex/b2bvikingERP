<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        set_time_limit(300);
        return Booking::with(['vendor', 'product', 'unit'])
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Booking No',
            'Vendor',
            'Product Name',
            'Variants',
            'Qty',
            'Shipping Method',
            'Status',
            'Date',
        ];
    }

    public function map($booking): array
    {
        $variantStr = '';
        if ($booking->variant_info) {
            $parts = [];
            foreach ($booking->variant_info as $name => $qty) {
                $parts[] = $name . ': ' . $qty;
            }
            $variantStr = implode(', ', $parts);
        }

        return [
            $booking->booking_no,
            $booking->vendor?->shop_name ?? 'N/A',
            $booking->product?->name ?? 'N/A',
            $variantStr ?: '—',
            $booking->qty,
            $booking->shipping_method ?? 'N/A',
            ucfirst($booking->status),
            $booking->created_at->format('Y-m-d'),
        ];
    }
}
