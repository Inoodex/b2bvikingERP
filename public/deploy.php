<?php

/**
 * Enterprise Deploy Webhook — b2bvikingERP
 * Location: public/deploy.php
 * URL: https://test.b2bviking.com/deploy.php
 *
 * This script is triggered by GitHub Actions after FTP upload.
 * It unpacks release.zip, runs migrations, and optimizes Laravel caches.
 */

// -----------------------------------------------------------------------------
// 1. CONFIGURATION & AUTHENTICATION
// -----------------------------------------------------------------------------

define('DEPLOY_SECRET', 'B2BViking@Deploy2026'); // Matches GitHub Secret: DEPLOY_SECRET

header('Content-Type: application/json; charset=utf-8');

$providedSecret = $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';
if (!hash_equals(DEPLOY_SECRET, $providedSecret)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: Invalid deploy secret']);
    exit;
}

// Ignore user abort and allow sufficient execution time
ignore_user_abort(true);
set_time_limit(300);

// Since this file is in public/, dirname(__DIR__) is the project root
$projectPath = dirname(__DIR__);
$log = [];

function runCmd(string $command, string $workingDir): array
{
    $cmd = "cd " . escapeshellarg($workingDir) . " && {$command} 2>&1";
    $output = [];
    $returnCode = 0;
    exec($cmd, $output, $returnCode);
    return [
        'command'     => $command,
        'exit_code'   => $returnCode,
        'output'      => trim(implode("\n", $output)),
    ];
}

// -----------------------------------------------------------------------------
// 2. UNPACK RELEASE ARCHIVE (release.zip)
// -----------------------------------------------------------------------------

$releaseZipPath = $projectPath . '/release.zip';

if (file_exists($releaseZipPath)) {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        $res = $zip->open($releaseZipPath);
        if ($res === true) {
            $zip->extractTo($projectPath);
            $zip->close();
            @unlink($releaseZipPath);
            $log[] = [
                'step'   => 'unpack_release',
                'status' => 'success',
                'note'   => 'release.zip extracted successfully into project root',
            ];
        } else {
            $log[] = [
                'step'   => 'unpack_release',
                'status' => 'error',
                'note'   => "Failed to open release.zip (Error Code: {$res})",
            ];
        }
    } else {
        // Fallback: unzip via shell if ZipArchive is not enabled
        $log[] = runCmd("unzip -o release.zip && rm -f release.zip", $projectPath);
    }
} else {
    $log[] = [
        'step'   => 'unpack_release',
        'status' => 'skipped',
        'note'   => 'release.zip not found (standalone sync or already extracted)',
    ];
}

// -----------------------------------------------------------------------------
// 3. UNPACK VENDOR ARCHIVE (If vendor.zip was included)
// -----------------------------------------------------------------------------

$vendorZipPath = $projectPath . '/vendor.zip';

if (file_exists($vendorZipPath)) {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($vendorZipPath) === true) {
            $zip->extractTo($projectPath . '/vendor');
            $zip->close();
            @unlink($vendorZipPath);
            $log[] = [
                'step'   => 'unpack_vendor',
                'status' => 'success',
                'note'   => 'vendor.zip extracted successfully into vendor/',
            ];
        }
    }
}

// -----------------------------------------------------------------------------
// 4. ARTISAN LIFECYCLE & CACHE OPTIMIZATION
// -----------------------------------------------------------------------------

try {
    // Run database migrations (--force skips confirmation in production)
    $log[] = runCmd('php artisan migrate --force', $projectPath);

    // Clear and rebuild all application caches
    $log[] = runCmd('php artisan optimize:clear', $projectPath);
    $log[] = runCmd('php artisan config:cache', $projectPath);
    $log[] = runCmd('php artisan route:cache', $projectPath);
    $log[] = runCmd('php artisan view:cache', $projectPath);

    // Ensure symbolic link for storage is intact
    $log[] = runCmd('php artisan storage:link --force', $projectPath);

    // Restart queue workers
    $log[] = runCmd('php artisan queue:restart || true', $projectPath);

} catch (\Throwable $e) {
    $log[] = [
        'step'   => 'artisan_execution',
        'status' => 'exception',
        'error'  => $e->getMessage(),
    ];
}

// -----------------------------------------------------------------------------
// 5. DEPLOYMENT LOGGING & RESPONSE
// -----------------------------------------------------------------------------

$logDir = $projectPath . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp'   => date('Y-m-d H:i:s'),
    'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'results'     => $log,
];

@file_put_contents(
    $logDir . '/deploy.log',
    json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL,
    FILE_APPEND
);

http_response_code(200);
echo json_encode([
    'status'      => 'success',
    'deployed_at' => date('Y-m-d H:i:s'),
    'log'         => $log,
]);
