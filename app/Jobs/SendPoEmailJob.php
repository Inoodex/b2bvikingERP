<?php

namespace App\Jobs;

use App\Mail\PoNotificationMail;
use App\Models\PoEmailLog;
use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPoEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Purchase $purchase;
    public string $recipientEmail;
    public ?string $notes;

    /**
     * Create a new job instance.
     */
    public function __construct(Purchase $purchase, string $recipientEmail, ?string $notes = null)
    {
        $this->purchase = $purchase;
        $this->recipientEmail = $recipientEmail;
        $this->notes = $notes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipientEmail)->send(new PoNotificationMail($this->purchase, $this->purchase->vendor));

            PoEmailLog::create([
                'purchase_id' => $this->purchase->id,
                'recipient_email' => $this->recipientEmail,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            PoEmailLog::create([
                'purchase_id' => $this->purchase->id,
                'recipient_email' => $this->recipientEmail,
                'status' => 'failed',
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
