<?php

/**
 * Post-Deploy Script — b2bvikingERP
 *
 * This script is called by GitHub Actions after FTP upload completes.
 * It runs Laravel artisan commands to finalize the deployment.
 *
 * Place this file at: public_html/deploy.php
 * URL: https://yourdomain.com/deploy.php
 */

// -------------------------------------------------------
// CONFIG — update these two values before uploading
// -------------------------------------------------------

define('DEPLOY_SECRET', 'B2BViking@Deploy2026');           // Must match GitHub Secret: DEPLOY_SECRET
define('PROJECT_PATH',  '/home/hyggznzm/test.b2bviking.com');      // Your project path on Namecheap server

// -------------------------------------------------------
// SECURITY
// Verify the request came from GitHub Actions using a secret token.
// Anyone else calling this URL will get a 403 Forbidden response.
// -------------------------------------------------------

$secret = $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';

if (!hash_equals(DEPLOY_SECRET, $secret)) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized']));
}

// -------------------------------------------------------
// HELPER — runs a shell command inside the project directory
// -------------------------------------------------------

function cmd(string $command, string $path): string
{
    $output = shell_exec("cd {$path} && {$command} 2>&1");
    return trim($output ?? 'no output');
}

// -------------------------------------------------------
// DEPLOY — run Laravel artisan commands one by one
// -------------------------------------------------------

$path = PROJECT_PATH;
$log  = [];

// Clear and rebuild config cache (makes app faster)
$log[] = ['command' => 'config:cache',  'output' => cmd('php artisan config:cache',        $path)];

// Clear and rebuild route cache (makes routing faster)
$log[] = ['command' => 'route:cache',   'output' => cmd('php artisan route:cache',         $path)];

// Clear and rebuild view cache (makes blade templates faster)
$log[] = ['command' => 'view:cache',    'output' => cmd('php artisan view:cache',          $path)];

// Run any new database migrations (--force skips confirmation in production)
$log[] = ['command' => 'migrate',       'output' => cmd('php artisan migrate --force',     $path)];

// Optimize the application (combines config + route cache)
$log[] = ['command' => 'optimize',      'output' => cmd('php artisan optimize',            $path)];

// Restart queue workers so they pick up new code changes
$log[] = ['command' => 'queue:restart', 'output' => cmd('php artisan queue:restart',       $path)];

// Create symbolic link from public/storage to storage/app/public
$log[] = ['command' => 'storage:link',  'output' => cmd('php artisan storage:link --force', $path)];

// -------------------------------------------------------
// LOGGING — save all command output to storage/logs/deploy.log
// -------------------------------------------------------

file_put_contents(
    $path . '/storage/logs/deploy.log',
    date('Y-m-d H:i:s') . PHP_EOL . json_encode($log, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL,
    FILE_APPEND
);

// -------------------------------------------------------
// RESPONSE — return success response to GitHub Actions
// -------------------------------------------------------

http_response_code(200);
echo json_encode([
    'status'      => 'ok',
    'deployed_at' => date('Y-m-d H:i:s'),
    'log'         => $log,
]);
