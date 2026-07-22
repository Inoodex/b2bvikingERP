<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchProductAnnouncementChunksJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;
    public int $uniqueFor = 3600;

    /**
     * @param array<int, int> $productIds
     */
    public function __construct(
        public array $productIds,
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
        $this->source = in_array($this->source, ['created', 'imported', 'manual'], true) ? $this->source : 'created';
        $this->customSubject = $this->normalizeNullableText($this->customSubject, 255);
        $this->customMessage = $this->normalizeNullableText($this->customMessage, 5000);
        $this->campaignId = $this->normalizeNullableText($this->campaignId, 255);
    }

    public function uniqueId(): string
    {
        $campaignToken = $this->campaignId ?: 'auto';
        return 'dispatch-product-announcement:' . $this->source . ':' . $campaignToken . ':' . sha1(json_encode($this->productIds));
    }

    public function handle(): void
    {
        $validProductIds = Product::query()
            ->whereIn('id', $this->productIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $validProductIds = array_values(array_unique($validProductIds));
        sort($validProductIds);

        if (empty($validProductIds)) {
            return;
        }

        User::query()
            ->select('id')
            ->where('status', 1)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->chunkById(250, function ($users) use ($validProductIds): void {
                $recipientIds = $users->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();
                $recipientIds = array_values(array_unique($recipientIds));
                sort($recipientIds);

                if (empty($recipientIds)) {
                    return;
                }

                SendProductAnnouncementChunkJob::dispatch(
                    $validProductIds,
                    $recipientIds,
                    $this->source,
                    $this->actorId,
                    $this->customSubject,
                    $this->customMessage,
                    $this->campaignId
                )->onConnection('database')->onQueue('mail-notifications');
            });
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
