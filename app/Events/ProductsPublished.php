<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductsPublished
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<int, int|string> $productIds
     */
    public function __construct(
        public array $productIds,
        public string $source = 'created',
        public ?int $actorId = null
    ) {
        $this->productIds = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($productIds, static fn ($id): bool => (int) $id > 0)
        )));

        $this->source = in_array($source, ['created', 'imported'], true) ? $source : 'created';
    }
}
