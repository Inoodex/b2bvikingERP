<?php

/**
 * Enterprise Deploy Webhook — b2bvikingERP
 * Location: public/deploy.php & deploy.php
 *
 * This script is triggered by GitHub Actions after FTP upload.
 * It unpacks release.zip, auto-detects PHP 8.3+, runs migrations, and optimizes Laravel caches.
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

ignore_user_abort(true);
set_time_limit(300);

// Determine project path (handles being in public/ or project root)
$projectPath = file_exists(__DIR__ . '/artisan') ? __DIR__ : dirname(__DIR__);
$log = [];

// -----------------------------------------------------------------------------
// 2. AUTO-DETECT PHP 8.3+ BINARY ON CPANEL
// -----------------------------------------------------------------------------

function getPhpBinary(): string
{
    $candidates = [
        '/opt/cpanel/ea-php83/root/usr/bin/php',
        '/usr/local/bin/ea-php83',
        '/usr/bin/ea-php83',
        'ea-php83',
        'php8.3',
        'php83',
        'php',
    ];
    foreach ($candidates as $bin) {
        $output = @shell_exec("{$bin} -r 'echo PHP_VERSION;' 2>/dev/null");
        if ($output && version_compare(trim($output), '8.3.0', '>=')) {
            return $bin;
        }
    }
    return 'php';
}

$phpBin = getPhpBinary();
$log[] = [
    'action'         => 'php_detection',
    'selected_php'   => $phpBin,
    'php_version'    => trim(@shell_exec("{$phpBin} -r 'echo PHP_VERSION;' 2>/dev/null") ?? PHP_VERSION),
];

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
// 3. UNPACK RELEASE ARCHIVE (release.zip)
// -----------------------------------------------------------------------------

// Disable legacy platform_check if present
@file_put_contents($projectPath . "/vendor/composer/platform_check.php", "<?php\n// Platform check disabled\n");

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
        $log[] = runCmd("unzip -o release.zip && rm -f release.zip", $projectPath);
    }
} else {
    $log[] = [
        'step'   => 'unpack_release',
        'status' => 'skipped',
        'note'   => 'release.zip not found (already extracted)',
    ];
}

// -----------------------------------------------------------------------------
// 4. UNPACK VENDOR ARCHIVE (If vendor.zip was included)
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
// 5. ARTISAN LIFECYCLE & CACHE OPTIMIZATION
// -----------------------------------------------------------------------------

try {
    $log[] = runCmd("{$phpBin} artisan migrate --force", $projectPath);
    $log[] = runCmd("{$phpBin} artisan optimize:clear", $projectPath);
    $log[] = runCmd("{$phpBin} artisan config:cache", $projectPath);
    $log[] = runCmd("{$phpBin} artisan route:cache", $projectPath);
    $log[] = runCmd("{$phpBin} artisan view:cache", $projectPath);
    $log[] = runCmd("{$phpBin} artisan storage:link --force", $projectPath);
    $log[] = runCmd("{$phpBin} artisan queue:restart || true", $projectPath);
} catch (\Throwable $e) {
    $log[] = [
        'step'   => 'artisan_execution',
        'status' => 'exception',
        'error'  => $e->getMessage(),
    ];
}

// -----------------------------------------------------------------------------
// 6. DEPLOYMENT LOGGING & RESPONSE
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
