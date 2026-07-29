<?php

namespace App\Mail;

use App\Models\Purchase;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PoNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Purchase $po;
    public Vendor $vendor;

    public function __construct(Purchase $po, Vendor $vendor)
    {
        $this->po = $po;
        $this->vendor = $vendor;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Purchase Order Issued: ' . ($this->po->po_no ?? ('PO-' . $this->po->id)),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.po_notification',
            with: [
                'po' => $this->po,
                'vendor' => $this->vendor,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('backend.purchase.po_pdf', ['po' => $this->po->load(['vendor', 'currency', 'items.product', 'items.variant'])]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'PO-' . ($this->po->po_no ?? $this->po->id) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
