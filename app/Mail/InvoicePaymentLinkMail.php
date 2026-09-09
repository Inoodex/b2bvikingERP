<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\SalesInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public SalesInvoice $invoice;
    public GeneralSetting $settings;
    public string $paymentUrl;

    public function __construct(SalesInvoice $invoice)
    {
        $this->invoice = $invoice->load(['order.user', 'items.product']);
        $this->settings = GeneralSetting::first() ?? new GeneralSetting();
        $this->paymentUrl = $invoice->public_payment_url;
    }

    public function envelope(): Envelope
    {
        $fromName = $this->settings->site_name ?? config('app.name');
        $fromEmail = $this->settings->contact_email ?? config('mail.from.address');

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            subject: 'Invoice #' . $this->invoice->invoice_no . ' Payment Request — ' . $fromName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invoice_payment_link',
        );
    }
}
