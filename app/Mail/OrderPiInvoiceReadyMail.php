<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPiInvoiceReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public GeneralSetting $settings;
    public string $viewUrl;
    public string $downloadUrl;
    public bool $attachPdf;

    public function __construct(Order $order, string $viewUrl, string $downloadUrl, bool $attachPdf = false)
    {
        $this->order = $order->load([
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
            'user',
        ]);

        $this->settings = GeneralSetting::first() ?? new GeneralSetting();
        $this->viewUrl = $viewUrl;
        $this->downloadUrl = $downloadUrl;
        $this->attachPdf = $attachPdf;
    }

    public function envelope(): Envelope
    {
        $fromName = $this->settings->site_name ?? config('app.name');
        $fromEmail = $this->settings->contact_email ?? config('mail.from.address');

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            subject: 'Proforma Invoice Ready — Order #' . $this->order->order_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pi_invoice_ready',
            with: [
                'recipientName' => $this->order->billing_name ?: ($this->order->user?->name ?? 'Customer'),
                'contextLabel' => 'Order',
                'referenceNo' => $this->order->order_no,
                'viewUrl' => $this->viewUrl,
                'downloadUrl' => $this->downloadUrl,
                'siteName' => $this->settings->site_name ?? config('app.name'),
                'supportEmail' => $this->settings->contact_email ?? config('mail.from.address'),
                'attachPdf' => $this->attachPdf,
            ],
        );
    }

    public function attachments(): array
    {
        if (!$this->attachPdf) {
            return [];
        }

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.orders.print_pdf', [
            'order' => $this->order,
            'settings' => $this->settings,
        ]);

        $filename = 'pi-invoice-' . $this->order->order_no . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
