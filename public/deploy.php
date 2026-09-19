<?php

/**
 * Enterprise Deploy Webhook — b2bvikingERP
 * Location: public/deploy.php
 * URL: https://test.b2bviking.com/deploy.php
 *
 * This script is triggered by GitHub Actions after deployment.
 * It unpacks vendor archives (if updated), runs migrations, and optimizes Laravel.
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

// -----------------------------------------------------------------------------
// 2. ENVIRONMENT & PATH INITIALIZATION
// -----------------------------------------------------------------------------

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
// 3. VENDOR ARCHIVE EXTRACTION (If new packages were deployed)
// -----------------------------------------------------------------------------

$vendorZipPath = $projectPath . '/vendor.zip';

if (file_exists($vendorZipPath) && class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    $res = $zip->open($vendorZipPath);
    if ($res === true) {
        $zip->extractTo($projectPath . '/vendor');
        $zip->close();
        @unlink($vendorZipPath);
        $log[] = [
            'action' => 'vendor_extract',
            'status' => 'success',
            'note'   => 'vendor.zip extracted successfully into vendor/',
        ];
    } else {
        $log[] = [
            'action' => 'vendor_extract',
            'status' => 'error',
            'note'   => "Failed to open vendor.zip (Code: {$res})",
        ];
    }
}

// -----------------------------------------------------------------------------
// 4. ARTISAN LIFECYCLE & CACHE OPTIMIZATION
// -----------------------------------------------------------------------------

try {
    // Put application into maintenance mode temporarily
    $log[] = runCmd('php artisan down --render="errors::503" || true', $projectPath);

    // Run database migrations
    $log[] = runCmd('php artisan migrate --force', $projectPath);

    // Clear and rebuild all application caches
    $log[] = runCmd('php artisan optimize:clear', $projectPath);
    $log[] = runCmd('php artisan config:cache', $projectPath);
    $log[] = runCmd('php artisan route:cache', $projectPath);
    $log[] = runCmd('php artisan view:cache', $projectPath);

    // Ensure symbolic link for storage is intact
    $log[] = runCmd('php artisan storage:link --force', $projectPath);

    // Restart queue workers to pick up fresh code
    $log[] = runCmd('php artisan queue:restart || true', $projectPath);

} finally {
    // ALWAYS bring application back online even if any command threw an error
    $log[] = runCmd('php artisan up', $projectPath);
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
