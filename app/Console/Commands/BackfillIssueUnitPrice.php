<?php

namespace App\Console\Commands;

use App\Models\IssueItem;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillIssueUnitPrice extends Command
{
    protected $signature = 'issue:backfill-unit-price';
    protected $description = 'Backfill issue_items.unit_price for historical records';

    public function handle()
    {
        $this->info('Backfilling linked issue items from order_items...');
        $linked = DB::statement("
            UPDATE issue_items ii
            INNER JOIN issues i ON ii.issue_id = i.id
            INNER JOIN order_items oi ON i.order_id = oi.order_id AND ii.product_id = oi.product_id
                AND (ii.variant_id = oi.variant_id OR (ii.variant_id IS NULL AND oi.variant_id IS NULL))
            SET ii.unit_price = oi.unit_price
            WHERE i.order_id IS NOT NULL AND (ii.unit_price IS NULL OR ii.unit_price = 0)
        ");
        $this->info("Linked items updated: " . ($linked === false ? 0 : $linked));

        $this->info('Backfilling standalone issue items from products.price...');
        $standalone = DB::statement("
            UPDATE issue_items ii
            INNER JOIN issues i ON ii.issue_id = i.id
            INNER JOIN products p ON ii.product_id = p.id
            SET ii.unit_price = COALESCE(NULLIF(p.price, 0), p.purchase_price, 0)
            WHERE i.order_id IS NULL AND (ii.unit_price IS NULL OR ii.unit_price = 0)
        ");
        $this->info("Standalone items updated: " . ($standalone === false ? 0 : $standalone));

        $remaining = IssueItem::whereNull('unit_price')->orWhere('unit_price', 0)->count();
        if ($remaining > 0) {
            $this->warn("$remaining items still at 0 — setting to 0 (no price data available)");
            IssueItem::whereNull('unit_price')->orWhere('unit_price', 0)->update(['unit_price' => 0]);
        }

        $total = IssueItem::count();
        $nonNull = IssueItem::whereNotNull('unit_price')->count();
        $this->info("Done! $nonNull / $total issue_items have unit_price set.");
    }
}
