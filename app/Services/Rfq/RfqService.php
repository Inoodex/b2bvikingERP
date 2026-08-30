<?php

namespace App\Services\Rfq;

use App\Models\Rfq;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RfqNotificationMail;

class RfqService
{
    public function storeRfq(array $data): Rfq
    {
        return DB::transaction(function () use ($data) {
            $rfq = Rfq::create([
                'rfq_no' => $data['rfq_no'],
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'status' => 'open',
            ]);

            foreach ($data['items'] as $item) {
                $rfq->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                ]);
            }

            foreach ($data['vendors'] as $vendorId) {
                $rfqVendor = $rfq->vendors()->create([
                    'vendor_id' => $vendorId,
                    'invited_at' => now(),
                ]);
                $rfqVendor->load('vendor');
                
                // Dispatch Email Notification if Vendor has email
                if ($rfqVendor->vendor && $rfqVendor->vendor->email) {
                    dispatch(function () use ($rfq, $rfqVendor) {
                        try {
                            Mail::to($rfqVendor->vendor->email)->send(new RfqNotificationMail($rfq, $rfqVendor->vendor));
                        } catch (\Exception $e) {
                            // Log or ignore if SMTP fails
                        }
                    })->afterResponse();
                }
            }

            // Clear user's procurement cart upon creating RFQ
            if (auth()->check()) {
                \App\Models\Cart::where('user_id', auth()->id())
                    ->where('cart_type', 'booking')
                    ->delete();
            }

            return $rfq;
        });
    }

    public function updateRfq(Rfq $rfq, array $data): Rfq
    {
        return DB::transaction(function () use ($rfq, $data) {
            $rfq->update([
                'rfq_no' => $data['rfq_no'],
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            // Re-sync items
            $rfq->items()->delete();
            foreach ($data['items'] as $item) {
                $rfq->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                ]);
            }

            // Re-sync vendors
            $rfq->vendors()->delete();
            foreach ($data['vendors'] as $vendorId) {
                $rfq->vendors()->create([
                    'vendor_id' => $vendorId,
                    'invited_at' => now(), // keeping it simple for now
                ]);
            }

            return $rfq;
        });
    }

    public function closeRfq(int $id): Rfq
    {
        return DB::transaction(function () use ($id) {
            // Pessimistic Locking to prevent Race Conditions
            // If someone is submitting a quotation, it will wait or fail safely.
            $rfq = Rfq::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($rfq->status === 'closed') {
                throw new \Exception('RFQ is already closed.');
            }

            $rfq->status = 'closed';
            $rfq->save();

            return $rfq;
        });
    }
}
