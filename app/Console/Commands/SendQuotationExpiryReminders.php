<?php

namespace App\Console\Commands;

use App\Models\SalesQuotation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendQuotationExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:quotation-expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for Sales Quotations expiring within 3 days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDate = Carbon::now()->addDays(3)->toDateString();

        $expiringQuotes = SalesQuotation::where('status', 'sent')
            ->where('reminder_sent', false)
            ->whereDate('valid_until', '<=', $targetDate)
            ->whereDate('valid_until', '>=', Carbon::now()->toDateString())
            ->get();

        $count = 0;
        foreach ($expiringQuotes as $quote) {
            // Update reminder status
            $quote->update(['reminder_sent' => true]);
            $count++;
        }

        $this->info("Sent {$count} quotation expiry reminders.");

        return Command::SUCCESS;
    }
}
