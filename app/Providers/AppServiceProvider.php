<?php

namespace App\Providers;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // \Illuminate\Support\Facades\View::share('settings', getSettings());
        $settings = null;
        try {
            if (Schema::hasTable('general_settings')) {
                $settings = GeneralSetting::first();

                if ($settings) {
                    // Override legacy currency with the new Master Setup Base Currency
                    $baseCurrency = \App\Models\Currency::where('is_base', true)->first();
                    if ($baseCurrency) {
                        $settings->currency_name = $baseCurrency->name;
                        $settings->currency_icon = $baseCurrency->symbol;
                        $settings->base_currency_name = $baseCurrency->name;
                        $settings->base_currency_icon = $baseCurrency->symbol;
                    }

                    config([
                        'mail.default' => $settings->mail_mailer ?: config('mail.default'),
                        'mail.mailers.smtp.host' => $settings->mail_host ?: config('mail.mailers.smtp.host'),
                        'mail.mailers.smtp.port' => $settings->mail_port ?: config('mail.mailers.smtp.port'),
                        'mail.mailers.smtp.username' => $settings->mail_username ?: config('mail.mailers.smtp.username'),
                        'mail.mailers.smtp.password' => $settings->mail_password ?: config('mail.mailers.smtp.password'),
                        'mail.mailers.smtp.encryption' => $settings->mail_encryption ?: config('mail.mailers.smtp.encryption'),
                        'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
                        'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Keep app booting (especially artisan commands) when DB is temporarily unavailable.
        }
        view()->share('settings', $settings);

        $this->registerGoogleDriveStorage();
        $this->ensureGoogleBackupDirectory();

        // \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
        //     return $user->hasRole('Admin') ? true : null;
        // });

        Paginator::useBootstrapFour();
    }

    private function registerGoogleDriveStorage(): void
    {
        // Register Google Drive Storage Driver
        try {
            Storage::extend('google', function ($app, $config) {
                $options = [];

                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                if (!empty($config['sharedFolderId'] ?? null)) {
                    $options['sharedFolderId'] = $config['sharedFolderId'];
                }

                if (!empty($config['parameters'] ?? null)) {
                    $options['parameters'] = $config['parameters'];
                }

                if (array_key_exists('useDisplayPaths', $config)) {
                    $options['useDisplayPaths'] = (bool) $config['useDisplayPaths'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId'] ?? null);
                $client->setClientSecret($config['clientSecret'] ?? null);
                $client->refreshToken($config['refreshToken'] ?? null);

                if (!empty($config['applicationName'] ?? null)) {
                    $client->setApplicationName($config['applicationName']);
                }

                $service = new \Google\Service\Drive($client);
                $root = $config['folder'] ?? $config['folderId'] ?? '/';

                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $root, $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Throwable $e) {
            // Keep app booting if Google Drive configuration is incomplete.
        }
    }

    private function ensureGoogleBackupDirectory(): void
    {
        if (!app()->runningInConsole()) {
            return;
        }

        $argv = $_SERVER['argv'] ?? [];
        $command = $argv[1] ?? '';
        $backupCommands = ['backup:run', 'backup:clean', 'backup:monitor', 'backup:list'];

        if (!in_array($command, $backupCommands, true)) {
            return;
        }

        $disks = (array) config('backup.backup.destination.disks', []);
        if (!in_array('google', $disks, true)) {
            return;
        }

        $backupName = (string) config('backup.backup.name', '');
        if ($backupName === '') {
            return;
        }

        try {
            Storage::disk('google')->makeDirectory($backupName);
        } catch (\Throwable $e) {
            // Ignore if Google Drive is not reachable or credentials are missing.
        }
    }
}
