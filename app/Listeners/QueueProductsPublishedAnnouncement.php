<?php

namespace App\Listeners;

use App\Events\ProductsPublished;
use App\Jobs\DispatchProductAnnouncementChunksJob;

class QueueProductsPublishedAnnouncement
{
    public function handle(ProductsPublished $event): void
    {
        if (empty($event->productIds)) {
            return;
        }

        DispatchProductAnnouncementChunksJob::dispatch(
            $event->productIds,
            $event->source,
            $event->actorId
        )->onConnection('database')->onQueue('mail-notifications');
    }
}
