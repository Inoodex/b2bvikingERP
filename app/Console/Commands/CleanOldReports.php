<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldReports extends Command
{
    protected $signature = 'reports:clean';
    protected $description = 'Delete report PDFs older than 10 minutes';

    public function handle()
    {
        $disk = Storage::disk('public');
        $files = $disk->files('reports');
        $now = now();
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            $age = $now->diffInMinutes(\Carbon\Carbon::createFromTimestamp($lastModified));
            if ($age > 10) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Cleaned {$deleted} old report PDFs.");
    }
}
