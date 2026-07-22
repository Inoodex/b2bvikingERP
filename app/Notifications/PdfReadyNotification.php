<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PdfReadyNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Saving to database so it shows up in the UI bell icon
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = 'PDF Ready';
        $message = 'Your PDF is ready to download.';
        $link = '#';

        if ($this->type === 'invoice') {
            $title = 'Invoice Ready';
            $message = "Invoice for Order #{$this->order->order_no} is ready.";
            $link = route('admin.orders.download-invoice', $this->order->id);
        } elseif ($this->type === 'pi_invoice') {
            $title = 'PI Invoice Ready';
            $message = "PI Invoice for Order #{$this->order->order_no} is ready.";
            $link = route('admin.orders.pi-invoice.download', $this->order->id); 
        } elseif ($this->type === 'customer_invoice') {
            $title = 'Customer Invoice Ready';
            $message = "Customer Invoice for Order #{$this->order->order_no} is ready.";
            $link = route('admin.orders.download-customer-invoice', $this->order->id);
        } elseif ($this->type === 'failed') {
            $title = 'PDF Generation Failed';
            $message = "Failed to generate PDF for Order #{$this->order->order_no}. Please try again.";
            $link = '#';
        }

        return [
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'order_id' => $this->order->id,
            'type' => $this->type,
        ];
    }
}
