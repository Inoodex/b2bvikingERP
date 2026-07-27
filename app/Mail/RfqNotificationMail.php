<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Rfq;

class RfqNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rfq $rfq;
    public $vendor;

    /**
     * Create a new message instance.
     */
    public function __construct(Rfq $rfq, $vendor)
    {
        $this->rfq = $rfq;
        $this->vendor = $vendor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quotation Request - ' . $this->rfq->rfq_no,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'backend.mail.rfq-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('backend.rfq.pdf', [
            'rfq' => $this->rfq,
            'vendor' => $this->vendor
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'RFQ_' . $this->rfq->rfq_no . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
