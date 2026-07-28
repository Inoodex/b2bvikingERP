<?php

namespace App\Jobs;

use App\Models\Rfq;
use App\Models\Vendor;
use App\Mail\SendRfqMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRfqEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Rfq $rfq;
    public int $vendorId;

    public function __construct(Rfq $rfq, int $vendorId)
    {
        $this->rfq = $rfq;
        $this->vendorId = $vendorId;
    }

    public function handle(): void
    {
        $vendor = Vendor::find($this->vendorId);
        if (!$vendor || empty($vendor->email)) {
            Log::warning("Cannot send RFQ email: Vendor ID {$this->vendorId} missing or has no email.");
            return;
        }

        try {
            Mail::to($vendor->email)->send(new SendRfqMail($this->rfq, $vendor));
            Log::info("RFQ {$this->rfq->rfq_no} email sent to vendor {$vendor->email}.");
        } catch (\Throwable $e) {
            Log::error("Failed to send RFQ {$this->rfq->rfq_no} email to vendor {$vendor->email}: " . $e->getMessage());
        }
    }
}
