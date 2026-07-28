<?php

namespace App\Mail;

use App\Models\Rfq;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class SendRfqMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rfq $rfq;
    public Vendor $vendor;

    public function __construct(Rfq $rfq, Vendor $vendor)
    {
        $this->rfq = $rfq;
        $this->vendor = $vendor;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Request for Quotation: ' . $this->rfq->rfq_no . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rfq_invitation',
            with: [
                'rfq' => $this->rfq,
                'vendor' => $this->vendor,
            ],
        );
    }

    public function attachments(): array
    {
        // Generate PDF on the fly
        $pdf = Pdf::loadView('backend.rfq.pdf', ['rfq' => $this->rfq, 'vendor' => $this->vendor]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'RFQ-' . $this->rfq->rfq_no . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
