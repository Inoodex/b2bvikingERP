<?php

namespace App\Services\Rfq;

use App\Models\Rfq;
use App\Models\VendorQuotation;
use Illuminate\Support\Facades\DB;

class VendorQuotationService
{
    public function storeQuotation(array $data): VendorQuotation
    {
        return DB::transaction(function () use ($data) {
            // Lock the RFQ row to ensure it doesn't get closed right while we submit
            $rfq = Rfq::where('id', $data['rfq_id'])->lockForUpdate()->firstOrFail();

            if ($rfq->status === 'closed') {
                throw new \Exception('Cannot submit quotation. This RFQ has been closed.');
            }

            $quotation = VendorQuotation::create([
                'rfq_id' => $rfq->id,
                'vendor_id' => $data['vendor_id'],
                'quotation_no' => $data['quotation_no'] ?? null,
                'currency_id' => $data['currency_id'] ?? null,
                'delivery_terms' => $data['delivery_terms'] ?? null,
                'validity_date' => $data['validity_date'] ?? null,
                'status' => 'received',
            ]);

            foreach ($data['items'] as $item) {
                $quotation->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            // Invalidate cached RFQ PDF
            \App\Support\PdfCacheManager::clearRfqCache($rfq->id);

            return $quotation;
        });
    }
}
