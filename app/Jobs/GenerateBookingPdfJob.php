<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Booking;
use App\Support\PdfImageHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateBookingPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $userId;
    public $timeout = 3600; // 1 hour timeout

    public function __construct($bookingId, $userId = null)
    {
        $this->bookingId = $bookingId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0); // Infinite time limit for CLI process

        $targetBooking = Booking::find($this->bookingId);
        if (!$targetBooking) {
            return;
        }

        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)
            ->with(['product.variants.color', 'product.variants.size', 'vendor', 'unit'])
            ->get();
        
        $settings = GeneralSetting::first();

        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        // $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 180, 46);
        $settings->optimized_logo = PdfImageHelper::optimize($logoPath, 480, 120, 95);

        foreach ($orderGroup as $item) {
            if ($item->product && $item->product->thumb_image) {
                // $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 60, 60);
                $item->product->optimized_image = PdfImageHelper::optimize($item->product->thumb_image, 400, 400, 95);
            }
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
            'enable_remote' => false,
        ])->loadView('backend.booking.print_pdf', compact('orderGroup', 'targetBooking', 'settings'));
        
        $path = 'bookings/booking_' . $targetBooking->booking_no . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // Notify user via Cache
        if ($this->userId) {
            $this->addCacheNotification($this->userId, [
                'type' => 'pdf_ready',
                'title' => 'Booking PDF Ready',
                'desc' => "PDF for Booking #{$targetBooking->booking_no} is ready.",
                'url' => route('admin.bookings.download-pdf', $targetBooking->id),
                'icon' => 'fas fa-book',
                'class' => 'bg-primary',
                'timestamp' => now()->timestamp,
            ]);
        }
    }

    private function addCacheNotification($userId, $data)
    {
        $key = 'user_pdf_notifications_' . $userId;
        $notifications = \Illuminate\Support\Facades\Cache::get($key, []);
        
        $data['time'] = now()->diffForHumans();
        $data['is_unread'] = true;
        $data['is_out_of_stock'] = false;
        
        $notifications[] = $data;
        $notifications = array_slice($notifications, -20);
        
        \Illuminate\Support\Facades\Cache::put($key, $notifications, now()->addDays(7));
    }
}
