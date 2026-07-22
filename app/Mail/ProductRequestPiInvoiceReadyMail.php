<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\ProductRequest;
use App\Support\PiInfoSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductRequestPiInvoiceReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public ProductRequest $productRequest;
    public GeneralSetting $settings;
    public string $viewUrl;
    public string $downloadUrl;
    public bool $attachPdf;

    private array $piInfo;
    private array $piTotals;
    private bool $hasSavedPiInfo;

    public function __construct(ProductRequest $productRequest, string $viewUrl, string $downloadUrl, bool $attachPdf = false)
    {
        $this->productRequest = $productRequest->load([
            'user',
            'order',
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
        ]);

        $this->settings = GeneralSetting::first() ?? new GeneralSetting();
        $this->viewUrl = $viewUrl;
        $this->downloadUrl = $downloadUrl;
        $this->attachPdf = $attachPdf;

        $this->piInfo = PiInfoSupport::prepare($this->productRequest->pi_info, $this->productRequest->items, 'qty');
        $this->piTotals = PiInfoSupport::summarize($this->piInfo);
        $this->hasSavedPiInfo = PiInfoSupport::hasContent($this->productRequest->pi_info);
    }

    public function envelope(): Envelope
    {
        $fromName = $this->settings->site_name ?? config('app.name');
        $fromEmail = $this->settings->contact_email ?? config('mail.from.address');

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            subject: 'Proforma Invoice Ready — Request #' . $this->productRequest->request_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pi_invoice_ready',
            with: [
                'recipientName' => $this->productRequest->user?->name ?? 'Customer',
                'contextLabel' => 'Request',
                'referenceNo' => $this->productRequest->request_no,
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
        ])->loadView('backend.product-request.print_pdf', [
            'productRequest' => $this->productRequest,
            'settings' => $this->settings,
            'piInfo' => $this->piInfo,
            'piTotals' => $this->piTotals,
            'hasSavedPiInfo' => $this->hasSavedPiInfo,
        ]);

        $filename = 'pi-invoice-' . $this->productRequest->request_no . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
