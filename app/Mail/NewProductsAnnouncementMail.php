<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProductsAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, array<string, mixed>> $products
     */
    public function __construct(
        public string $recipientName,
        public string $source,
        public int $totalProducts,
        public array $products,
        public int $hiddenCount = 0,
        public ?string $customSubject = null,
        public ?string $customMessage = null
    ) {
        $this->customSubject = $this->normalizeNullableText($this->customSubject, 255);
        $this->customMessage = $this->normalizeNullableText($this->customMessage, 5000);
    }

    public function envelope(): Envelope
    {
        $subject = $this->resolvedSubject();

        return new Envelope(
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.new_products_announcement',
            with: [
                'recipientName' => $this->recipientName,
                'source' => $this->source,
                'totalProducts' => $this->totalProducts,
                'products' => $this->products,
                'hiddenCount' => $this->hiddenCount,
                'announcementMessage' => $this->resolvedMessage(),
                'shopUrl' => url('/shop'),
            ]
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function resolvedSubject(): string
    {
        if (!empty($this->customSubject)) {
            return $this->customSubject;
        }

        $subject = $this->source === 'imported'
            ? 'New Products Imported (' . $this->totalProducts . ')'
            : ($this->source === 'manual'
                ? ($this->totalProducts === 1 ? 'Product Announcement' : 'Product Announcements (' . $this->totalProducts . ')')
                : ($this->totalProducts === 1
                    ? 'New Product Added'
                    : 'New Products Added (' . $this->totalProducts . ')'));

        return $subject . ' | ' . config('app.name');
    }

    private function resolvedMessage(): string
    {
        if (!empty($this->customMessage)) {
            return $this->customMessage;
        }

        return match ($this->source) {
            'imported' => 'Bulk import completed with new products.',
            'manual' => 'Selected products have been shared by admin. Please review the latest items below.',
            default => 'A new product has been added.',
        };
    }

    private function normalizeNullableText(?string $value, int $maxLength): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        return mb_substr($trimmed, 0, $maxLength);
    }
}
