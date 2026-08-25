<?php

namespace App\Services\Inventory;

use App\Models\StockBatch;
use App\Models\WarehouseBin;
use App\Models\Product;

class BarcodeGeneratorService
{
    /**
     * Generate barcode string for a warehouse bin.
     * Format: BIN-{outlet_id}-{zone_id}-{bin_id}
     */
    public function generateBinBarcode(WarehouseBin $bin): string
    {
        // Require the bin to have its zone loaded
        if (!$bin->relationLoaded('zone')) {
            $bin->load('zone');
        }

        $outletId = $bin->zone->outlet_id;
        $zoneId = $bin->zone_id;
        $binId = $bin->id;

        return "BIN-{$outletId}-{$zoneId}-{$binId}";
    }

    /**
     * Generate a unique QR Payload hash for a Stock Batch.
     * We encode JSON payload containing batch details and hash it,
     * or return a Base64 string for a QR generator to consume.
     */
    public function generateBatchBarcode(StockBatch $batch): string
    {
        $payload = [
            'batch_no' => $batch->batch_no,
            'product_id' => $batch->product_id,
            'landed_cost' => $batch->unit_cost,
            'received_date' => $batch->received_date,
            'timestamp' => now()->timestamp, // ensures uniqueness even if other params are same
        ];

        // We can just encode it in base64 to store in DB as the barcode string.
        // A QR code scanner will read this string, and the system can decode it.
        return base64_encode(json_encode($payload));
    }

    /**
     * Generate a generic unique barcode for a product if one doesn't exist.
     */
    public function generateProductBarcode(Product $product): string
    {
        // Format: PRD-{category_id}-{product_id}-{random}
        $catId = $product->category_id ?? 0;
        $prodId = $product->id;
        $rand = strtoupper(substr(md5(uniqid()), 0, 4));

        return "PRD-{$catId}-{$prodId}-{$rand}";
    }
}
