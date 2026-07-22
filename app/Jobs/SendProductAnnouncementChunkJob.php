<?php

namespace App\Jobs;

use App\Mail\NewProductsAnnouncementMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendProductAnnouncementChunkJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public int $uniqueFor = 3600;

    /**
     * @param array<int, int> $productIds
     * @param array<int, int> $recipientIds
     */
    public function __construct(
        public array $productIds,
        public array $recipientIds,
        public string $source = 'created',
        public ?int $actorId = null,
        public ?string $customSubject = null,
        public ?string $customMessage = null,
        public ?string $campaignId = null
    ) {
        $this->productIds = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($this->productIds, static fn ($id): bool => (int) $id > 0)
        )));
        sort($this->productIds);

        $this->recipientIds = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($this->recipientIds, static fn ($id): bool => (int) $id > 0)
        )));
        sort($this->recipientIds);

        $this->source = in_array($this->source, ['created', 'imported', 'manual'], true) ? $this->source : 'created';
        $this->customSubject = $this->normalizeNullableText($this->customSubject, 255);
        $this->customMessage = $this->normalizeNullableText($this->customMessage, 5000);
        $this->campaignId = $this->normalizeNullableText($this->campaignId, 255);
    }

    public function uniqueId(): string
    {
        $campaignToken = $this->campaignId ?: 'auto';
        return 'send-product-announcement:' . $this->source . ':' .
            $campaignToken . ':' . sha1(json_encode($this->productIds)) . ':' . sha1(json_encode($this->recipientIds));
    }

    public function handle(): void
    {
        $products = Product::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->whereIn('id', $this->productIds)
            ->orderByDesc('id')
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $productRows = $products->map(function (Product $product): array {
            return [
                'id' => (int) $product->id,
                'name' => (string) ($product->name ?? ''),
                'slug' => (string) ($product->slug ?? ''),
                'product_number' => (string) ($product->product_number ?? ''),
                'category' => (string) ($product->category?->name ?? 'N/A'),
                'brand' => (string) ($product->brand?->name ?? 'N/A'),
                'price' => (float) ($product->price ?? 0),
                'outlet_price' => (float) ($product->outlet_price ?? 0),
                'discount_label' => $this->formatRateLabel(
                    (string) ($product->discount_type ?? ''),
                    $product->discount
                ),
                'vat_label' => $this->formatRateLabel(
                    (string) ($product->vat_type ?? ''),
                    $product->vat_value
                ),
                'url' => !empty($product->slug) ? url('/product/' . $product->slug) : null,
            ];
        })->values();

        $limitedRows = $productRows->take(25)->values()->all();
        $totalProducts = $productRows->count();
        $hiddenCount = max(0, $totalProducts - count($limitedRows));

        $recipients = User::query()
            ->select(['id', 'name', 'email'])
            ->whereIn('id', $this->recipientIds)
            ->where('status', 1)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->get();

        // Prevent duplicate mails when multiple users share the same email.
        $uniqueRecipients = [];
        foreach ($recipients as $recipient) {
            $email = strtolower(trim((string) $recipient->email));
            if ($email === '' || isset($uniqueRecipients[$email])) {
                continue;
            }
            $uniqueRecipients[$email] = $recipient;
        }

        foreach ($uniqueRecipients as $recipient) {
            $normalizedEmail = strtolower(trim((string) $recipient->email));
            $mailLockKey = $this->recipientAnnouncementLockKey($normalizedEmail);

            // Hard dedupe: do not send the same product batch to the same email repeatedly.
            if (!Cache::add($mailLockKey, 1, now()->addDay())) {
                continue;
            }

            try {
                Mail::to($recipient->email)->send(new NewProductsAnnouncementMail(
                    recipientName: (string) ($recipient->name ?? 'Customer'),
                    source: $this->source,
                    totalProducts: $totalProducts,
                    products: $limitedRows,
                    hiddenCount: $hiddenCount,
                    customSubject: $this->customSubject,
                    customMessage: $this->customMessage
                ));
            } catch (\Throwable $e) {
                // Allow retry for this recipient if current send failed.
                Cache::forget($mailLockKey);
                $errorMessage = (string) $e->getMessage();
                Log::warning('Product announcement email failed', [
                    'user_id' => (int) $recipient->id,
                    'email' => (string) $recipient->email,
                    'source' => $this->source,
                    'error' => $errorMessage,
                ]);
            }
        }
    }

    private function recipientAnnouncementLockKey(string $normalizedEmail): string
    {
        $campaignToken = $this->campaignId ?: 'auto';
        return 'product-announcement:recipient:' . $this->source . ':' .
            $campaignToken . ':' . sha1(json_encode($this->productIds)) . ':' . sha1($normalizedEmail);
    }

    private function formatRateLabel(string $type, mixed $rawValue): ?string
    {
        $normalizedType = strtolower(trim($type));
        $value = max(0, (float) $rawValue);

        if ($value <= 0 || !in_array($normalizedType, ['flat', 'percent'], true)) {
            return null;
        }

        $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        return $normalizedType === 'percent' ? $formatted . '%' : 'Flat ' . $formatted;
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
