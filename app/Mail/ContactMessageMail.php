<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessageMail extends Mailable
{
    public function __construct(
        public array $contactMessage,
        public ?GeneralSetting $settings = null
    ) {
        $this->settings = $this->settings ?: GeneralSetting::first();
    }

    public function envelope(): Envelope
    {
        $fromEmail = $this->settings->mail_from_address ?? config('mail.from.address');
        $fromName = $this->settings->mail_from_name ?? config('mail.from.name');
        $subject = $this->contactMessage['subject'] ?? 'Contact Message';
        $replyToEmail = $this->contactMessage['email'] ?? null;
        $replyToName = trim((string) ($this->contactMessage['first_name'] ?? '') . ' ' . (string) ($this->contactMessage['last_name'] ?? ''));

        return new Envelope(
            subject: 'New Contact Message: ' . $subject,
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            replyTo: $replyToEmail ? [new \Illuminate\Mail\Mailables\Address($replyToEmail, $replyToName ?: $replyToEmail)] : []
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact_message',
            with: [
                'messageData' => $this->contactMessage,
                'settings' => $this->settings,
            ]
        );
    }
}
