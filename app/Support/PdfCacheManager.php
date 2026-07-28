<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfCacheManager
{
    /**
     * Check if a cached PDF exists and is still valid within the TTL (default: 3600 seconds / 1 hour).
     */
    public static function isFresh(string $path, int $ttlSeconds = 3600): bool
    {
        if (!Storage::disk('public')->exists($path)) {
            return false;
        }

        $lastModified = Storage::disk('public')->lastModified($path);
        return $lastModified >= (now()->timestamp - $ttlSeconds);
    }

    /**
     * Invalidate (delete) RFQ PDF cache.
     */
    public static function clearRfqCache(int|string $rfqId): void
    {
        $path = 'rfqs/rfq_' . $rfqId . '.pdf';
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info("Invalidated RFQ PDF cache: {$path}");
        }
    }

    /**
     * Invalidate (delete) CS PDF cache.
     */
    public static function clearCsCache(int|string $csId): void
    {
        $path = 'cs/cs_' . $csId . '.pdf';
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info("Invalidated CS PDF cache: {$path}");
        }
    }
}
