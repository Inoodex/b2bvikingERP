<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderNumberService
{
    /*
     * ─── OLD FORMAT (date-based, zero-padded) ───────────────────
     *   Example: DS-00001-062026
     *
     *   To restore the OLD format, comment out the NEW code below
     *   and UNCOMMENT this block:
     */
    /*
    public static function generate(string $prefix, string $modelClass, ?string $sequenceKey = null): string
    {
        $dateSuffix = now()->format('mY');
        $key = $sequenceKey ? $sequenceKey . '_' . $dateSuffix : $dateSuffix;

        return DB::transaction(function () use ($prefix, $dateSuffix, $key, $sequenceKey) {
            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->lockForUpdate()
                ->first();

            $current = DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->value('current_serial');

            if ($current === null) {
                $maxSerial = 0;

                if ($sequenceKey === 'issue_returns') {
                    $rows = DB::table('issue_returns')
                        ->where('return_no', 'LIKE', $prefix . '-_____-' . $dateSuffix)
                        ->orderBy('return_no', 'desc')
                        ->get(['return_no']);
                    foreach ($rows as $row) {
                        $parts = explode('-', $row->return_no);
                        $sn = (int) ($parts[count($parts) - 2] ?? 0);
                        if ($sn > $maxSerial) $maxSerial = $sn;
                    }
                } elseif ($sequenceKey) {
                    $rows = DB::table('bookings')
                        ->where('booking_no', 'LIKE', $prefix . '-_____-' . $dateSuffix)
                        ->orderBy('booking_no', 'desc')
                        ->get(['booking_no']);
                    foreach ($rows as $row) {
                        $parts = explode('-', $row->booking_no);
                        $sn = (int) ($parts[count($parts) - 2] ?? 0);
                        if ($sn > $maxSerial) $maxSerial = $sn;
                    }
                } else {
                    foreach (['orders' => 'order_no', 'product_requests' => 'request_no'] as $table => $col) {
                        $rows = DB::table($table)
                            ->where($col, 'LIKE', '%-_____-' . $dateSuffix)
                            ->orderBy($col, 'desc')
                            ->get([$col]);
                        foreach ($rows as $row) {
                            $parts = explode('-', $row->$col);
                            $sn = (int) ($parts[count($parts) - 2] ?? 0);
                            if ($sn > $maxSerial) $maxSerial = $sn;
                        }
                    }
                }

                DB::table('order_sequences')->insert([
                    'prefix_month' => $key,
                    'current_serial' => $maxSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $current = $maxSerial;
            }

            $next = $current + 1;
            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->update(['current_serial' => $next, 'updated_at' => now()]);

            $serial = str_pad($next, 5, '0', STR_PAD_LEFT);
            return "{$prefix}-{$serial}-{$dateSuffix}";
        });
    }
    */

    /**
     * ─── NEW FORMAT (WITHOUT scan — starts from 0) ─────────────
     *   Examples: DS-1, ORD-2, DS-REQ-3, VO-1, RET-1, CPR-4 ...
     *
     *   Skips any numbers that already exist in the DB.
     */
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

    /**
     * Check if a specific generated number already exists.
     */
    private static function numberExists(string $prefix, int $serial, ?string $sequenceKey): bool
    {
        $value = "{$prefix}-{$serial}";

        if ($sequenceKey === 'issue_returns') {
            return DB::table('issue_returns')->where('return_no', $value)->exists();
        }
        if ($sequenceKey === 'VO') {
            return DB::table('bookings')->where('booking_no', $value)->exists();
        }
        return DB::table('orders')->where('order_no', $value)->exists()
            || DB::table('product_requests')->where('request_no', $value)->exists();
    }

    /*
     * ─── NEW FORMAT (WITH scan — continues from existing data) ──
     *   Groups:
     *     Global (shared): DS/ORD/DS-REQ/REQ/CPR → key = 'order_prefix'
     *     Booking (VO):                           → key = 'VO'
     *     Issue Return (RET):                     → key = 'issue_returns'
     *
     *   Examples: DS-9, ORD-10, DS-REQ-11, VO-1, RET-1, CPR-12 ...
     *
     *   Scans existing data on every call to ensure the serial
     *   never falls behind records that already exist in the DB.
     *
     *   To use the "WITHOUT scan" version, comment this block out
     *   and uncomment the block above.
     *
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
                $maxSerial = self::scanExistingMaxSerial($key, $sequenceKey);

                DB::table('order_sequences')->insert([
                    'prefix_month' => $key,
                    'current_serial' => $maxSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $current = $maxSerial;
            } else {
                $maxSerial = self::scanExistingMaxSerial($key, $sequenceKey);
                if ($maxSerial > $current) {
                    DB::table('order_sequences')
                        ->where('prefix_month', $key)
                        ->update(['current_serial' => $maxSerial, 'updated_at' => now()]);
                    $current = $maxSerial;
                }
            }

            $next = $current + 1;
            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->update(['current_serial' => $next, 'updated_at' => now()]);

            return "{$prefix}-{$next}";
        });
    }
    */

    /**
     * Scan existing data for the highest serial number used so far.
     * Handles both old format (DS-00008-062026) and any new format
     * (DS-9) records that may already exist.
     */
    /*
    private static function scanExistingMaxSerial(string $key, ?string $sequenceKey): int
    {
        $maxSerial = 0;

        if ($sequenceKey === 'issue_returns' && Schema::hasTable('issue_returns')) {
            $rows = DB::table('issue_returns')
                ->where('return_no', 'LIKE', '%-%')
                ->orderBy('return_no', 'desc')
                ->get(['return_no']);
            foreach ($rows as $row) {
                $sn = self::extractSerial($row->return_no);
                if ($sn > $maxSerial) $maxSerial = $sn;
            }
        } elseif ($sequenceKey === 'VO' && Schema::hasTable('bookings')) {
            $rows = DB::table('bookings')
                ->where('booking_no', 'LIKE', '%-%')
                ->orderBy('booking_no', 'desc')
                ->get(['booking_no']);
            foreach ($rows as $row) {
                $sn = self::extractSerial($row->booking_no);
                if ($sn > $maxSerial) $maxSerial = $sn;
            }
        } else {
            $scanTables = ['orders' => 'order_no', 'product_requests' => 'request_no'];
            if (Schema::hasTable('custom_product_requests')) {
                $scanTables['custom_product_requests'] = 'request_no';
            }
            foreach ($scanTables as $table => $col) {
                $rows = DB::table($table)
                    ->where($col, 'LIKE', '%-%')
                    ->orderBy($col, 'desc')
                    ->get([$col]);
                foreach ($rows as $row) {
                    $sn = self::extractSerial($row->$col);
                    if ($sn > $maxSerial) $maxSerial = $sn;
                }
            }
        }

        return $maxSerial;
    }
    /*
    /**
     * Extract a numeric serial from various formats:
     *   Old: DS-00008-062026    → 8 (5-digit serial before 6-digit date)
     *   New: DS-9               → 9 (numeric after prefix)
     *   Random: DS-H4SIHMIVZO   → 0 (ignored)
     */

     /*
    private static function extractSerial(string $value): int
    {
        $parts = explode('-', $value);
        $cnt = count($parts);

        if ($cnt >= 3) {
            $candidate = $parts[$cnt - 2];
            $datePart = $parts[$cnt - 1];

            // Old format: ...-00008-062026 (5-digit serial + 6-digit date)
            if (preg_match('/^\d{5}$/', $candidate) && preg_match('/^\d{6}$/', $datePart)) {
                return (int) $candidate;
            }

            // New format with dash in prefix: DS-REQ-3
            if (preg_match('/^\d+$/', $datePart)) {
                return (int) $datePart;
            }
        }

        if ($cnt === 2) {
            // New format: prefix-9
            if (preg_match('/^\d+$/', $parts[1] ?? '')) {
                return (int) $parts[1];
            }
        }

        return 0;
    }
    */
}
