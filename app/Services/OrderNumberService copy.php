<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class OrderNumberService
{
    /**
     * Generate a unique, sequential order/request number.
     * Format: [Prefix]-00001-MMYYYY
     *
     * @param string $prefix The prefix to use (e.g., 'DS', 'ORD', 'REQ', 'DS-REQ')
     * @param string $modelClass The model class to count records from (e.g., \App\Models\Order::class)
     * @return string
     */
    public static function generate(string $prefix, string $modelClass): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $dateSuffix = $month . $year; // MMYYYY

        return DB::transaction(function () use ($prefix, $modelClass, $year, $month, $dateSuffix) {
            $model = new $modelClass;
            $table = $model->getTable();
            $column = Schema::hasColumn($table, 'order_no') ? 'order_no' : (Schema::hasColumn($table, 'request_no') ? 'request_no' : 'order_no');
            $pattern = $prefix . '-%-' . $dateSuffix;

            $count = DB::table($table)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where($column, 'LIKE', $pattern)
                ->lockForUpdate()
                ->count();

            $next = $count + 1;
            $serial = str_pad($next, 5, '0', STR_PAD_LEFT);

            return "{$prefix}-{$serial}-{$dateSuffix}";
        });
    }
}
