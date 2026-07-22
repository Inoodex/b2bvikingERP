<?php

namespace App\Providers;

use App\Events\ProductsPublished;
use App\Listeners\QueueProductsPublishedAnnouncement;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(ProductsPublished::class, QueueProductsPublishedAnnouncement::class);
    }
}
