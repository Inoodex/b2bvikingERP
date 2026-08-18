<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderNumberService
{
    public static function generate(string $prefix, string $modelClass, ?string $sequenceKey = null): string
    {
        $key = $sequenceKey ?? 'order_prefix';

        return DB::transaction(function () use ($prefix, $key, $sequenceKey) {
            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->lockForUpdate()
                ->first();

            $current = DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->value('current_serial');

            if ($current === null) {
                DB::table('order_sequences')->insert([
                    'prefix_month' => $key,
                    'current_serial' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $current = 0;
            }

            $next = $current + 1;
            while (self::numberExists($prefix, $next, $sequenceKey)) {
                $next++;
            }

            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->update(['current_serial' => $next, 'updated_at' => now()]);

            return "{$prefix}-{$next}";
        });
    }

    private static function numberExists(string $prefix, int $serial, ?string $sequenceKey): bool
    {
        $value = "{$prefix}-{$serial}";

        if ($sequenceKey === 'sales_returns') {
            return DB::table('sales_returns')->where('return_no', $value)->exists();
        }
        if ($sequenceKey === 'credit_notes') {
            return DB::table('credit_notes')->where('credit_note_no', $value)->exists();
        }
        if ($sequenceKey === 'issue_returns') {
            return DB::table('issue_returns')->where('return_no', $value)->exists();
        }
        if ($sequenceKey === 'VO') {
            return DB::table('bookings')->where('booking_no', $value)->exists();
        }
        return DB::table('orders')->where('order_no', $value)->exists()
            || DB::table('product_requests')->where('request_no', $value)->exists();
    }

    public static function generateSalesInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';
        $latest = DB::table('sales_invoices')
            ->where('invoice_no', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSerial = (int) substr($latest->invoice_no, strlen($prefix));
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1;
        }

        return $prefix . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);
    }

    public static function generateCustomerPaymentNumber(): string
    {
        $prefix = 'REC-' . now()->format('Ym') . '-';
        $latest = DB::table('customer_payments')
            ->where('payment_no', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSerial = (int) substr($latest->payment_no, strlen($prefix));
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1;
        }

        return $prefix . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);
    }
}
