<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


//setup corn live server
// * * * * * cd /home/USERNAME/your-project && /usr/local/bin/php artisan queue:work database --queue=mail-notifications,default --stop-when-empty --tries=3 --max-time=50 --sleep=1 --timeout=120 >> /dev/null 2>&1

// * * * * * cd /home/USERNAME/your-project && /usr/local/bin/php artisan queue:work database --queue=mail-notifications,default --stop-when-empty --tries=3 --max-time=50 --sleep=1 --timeout=120 >> /dev/null 2>&1

// * * * * * cd /home/USERNAME/your-project && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:clean')->dailyAt('02:30');
Schedule::command('backup:run')->dailyAt('03:00');

// Clean up generated invoice PDFs older than 24 hours
Schedule::call(function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $folders = ['invoices', 'purchases', 'bookings'];
    $now = now();
    
    foreach ($folders as $folder) {
        $files = $disk->files($folder);
        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            if ($now->diffInHours(\Carbon\Carbon::createFromTimestamp($lastModified)) >= 24) {
                $disk->delete($file);
            }
        }
    }
})->dailyAt('04:00')->name('clean-old-invoices')->withoutOverlapping();

// Clean up old report PDFs every minute (auto-refresh)
Schedule::command('reports:clean')->everyMinute()->name('clean-old-reports')->withoutOverlapping();

// Phase 4: Auto-Replenishment - Checks low stock & drafts POs daily at 01:00 AM
Schedule::command('inventory:auto-replenish')->dailyAt('01:00')->name('inventory-auto-replenish')->withoutOverlapping();

// Phase 4: Month-End Valuation Snapshot - Freezes monthly inventory valuation on last day of month at 23:59
Schedule::command('inventory:take-snapshot')->lastDayOfMonth('23:59')->name('inventory-snapshot-month-end')->withoutOverlapping();

