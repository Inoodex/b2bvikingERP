<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait HasEphemeralPdfReports
{
    /**
     * Get the latest generated ephemeral PDF file for this user and report type.
     */
    protected function getLatestReportFile(string $reportType, string $downloadRoute = 'admin.reports.payables-receivables.download'): ?array
    {
        $userId = auth()->id() ?? 0;
        $tempDir = storage_path('app/temp_reports');
        if (!File::exists($tempDir)) {
            return null;
        }

        $pattern = $tempDir . "/{$reportType}_u{$userId}_*.pdf";
        $files = glob($pattern);

        if (empty($files)) {
            return null;
        }

        // Sort descending by modified timestamp
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
        $latestFile = $files[0];

        return [
            'filename'  => basename($latestFile),
            'timestamp' => filemtime($latestFile),
            'time'      => date('h:i A', filemtime($latestFile)),
            'date'      => date('d M Y, h:i A', filemtime($latestFile)),
            'url'       => route($downloadRoute, ['file' => basename($latestFile)]),
        ];
    }
}
