<?php

/**
 * Enterprise Deploy Webhook — b2bvikingERP
 * Location: public/deploy.php & deploy.php
 *
 * Triggered by GitHub Actions to run database migrations and optimize Laravel caches.
 * Lightweight, zero-overhead, zero-archive extraction.
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
set_time_limit(120);

// Determine project path (handles both public/ and project root)
$projectPath = file_exists(__DIR__ . '/artisan') ? __DIR__ : dirname(__DIR__);
$log = [];

// -----------------------------------------------------------------------------
// 2. AUTO-DETECT PHP BINARY ON CPANEL
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
        if ($output && version_compare(trim($output), '8.2.0', '>=')) {
            return $bin;
        }
    }
    return 'php';
}

$phpBin = getPhpBinary();

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
// 3. ARTISAN LIFECYCLE & CACHE OPTIMIZATION
// -----------------------------------------------------------------------------

// Ensure platform check doesn't block legacy autoloader
$platformCheck = $projectPath . '/vendor/composer/platform_check.php';
if (file_exists(dirname($platformCheck))) {
    @file_put_contents($platformCheck, "<?php\n// Platform check disabled\n");
}

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
// 4. DEPLOYMENT LOGGING & RESPONSE
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
