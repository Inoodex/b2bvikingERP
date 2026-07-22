<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public int $expiresMinutes;
    public $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, int $expiresMinutes)
    {
        $this->code = $code;
        $this->expiresMinutes = $expiresMinutes;
        $this->settings = GeneralSetting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromName = $this->settings->site_name ?? config('app.name');
        $fromEmail = $this->settings->contact_email ?? config('mail.from.address');

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: 'Your Admin Login Verification Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.two_factor_code',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
