<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Support\AuditLogSupport;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class AuditAuthenticationListener
{
    /**
     * Handle user login event.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        $guard = $event->guard ?? 'web';
        $roleName = method_exists($user, 'getRoleNames') && $user->getRoleNames()->isNotEmpty()
            ? $user->getRoleNames()->first()
            : ($user->role ?? 'User');

        AuditLogSupport::log([
            'user_id'      => $user->id,
            'vendor_id'    => $user->vendor_id ?? null,
            'module'       => 'auth',
            'action'       => 'login',
            'entity_type'  => 'user',
            'entity_id'    => $user->id,
            'reference_no' => 'AUTH-LOG-' . $user->id,
            'description'  => "User {$user->name} ({$user->email}) successfully authenticated via guard [{$guard}] with role [{$roleName}]",
            'new_values'   => [
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $roleName,
                'guard'    => $guard,
                'remember' => (bool) $event->remember,
            ],
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ]);
    }

    /**
     * Handle user logout event.
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        $guard = $event->guard ?? 'web';

        AuditLogSupport::log([
            'user_id'      => $user->id,
            'vendor_id'    => $user->vendor_id ?? null,
            'module'       => 'auth',
            'action'       => 'logout',
            'entity_type'  => 'user',
            'entity_id'    => $user->id,
            'reference_no' => 'AUTH-OUT-' . $user->id,
            'description'  => "User {$user->name} ({$user->email}) signed out of session from guard [{$guard}]",
            'old_values'   => [
                'email' => $user->email,
                'guard' => $guard,
            ],
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ]);
    }

    /**
     * Handle failed authentication attempt.
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? ($event->credentials['username'] ?? 'Unknown');
        $user = $event->user;
        $guard = $event->guard ?? 'web';

        AuditLogSupport::log([
            'user_id'      => $user?->id ?? null,
            'vendor_id'    => $user?->vendor_id ?? null,
            'module'       => 'auth',
            'action'       => 'failed_login',
            'entity_type'  => 'user',
            'entity_id'    => $user?->id ?? null,
            'reference_no' => 'SEC-FAIL',
            'description'  => "Failed login attempt for identity [{$email}] via guard [{$guard}]",
            'new_values'   => [
                'attempted_identity' => $email,
                'guard'              => $guard,
                'ip'                 => request()?->ip(),
            ],
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ]);
    }

    /**
     * Handle rate-limit account lockout.
     */
    public function handleLockout(Lockout $event): void
    {
        $email = $event->request?->input('email') ?? 'Unknown';

        AuditLogSupport::log([
            'user_id'      => null,
            'module'       => 'auth',
            'action'       => 'account_lockout',
            'entity_type'  => 'security',
            'entity_id'    => null,
            'reference_no' => 'SEC-LOCKOUT',
            'description'  => "Authentication rate-limit exceeded: Lockout triggered for [{$email}]",
            'new_values'   => [
                'email' => $email,
                'ip'    => request()?->ip(),
            ],
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ]);
    }

    /**
     * Handle password reset event.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        AuditLogSupport::log([
            'user_id'      => $user->id,
            'vendor_id'    => $user->vendor_id ?? null,
            'module'       => 'auth',
            'action'       => 'password_reset',
            'entity_type'  => 'user',
            'entity_id'    => $user->id,
            'reference_no' => 'SEC-PWD-' . $user->id,
            'description'  => "Credentials updated: Password successfully reset for {$user->name} ({$user->email})",
            'new_values'   => [
                'email' => $user->email,
            ],
            'ip_address'   => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            Login::class         => 'handleLogin',
            Logout::class        => 'handleLogout',
            Failed::class        => 'handleFailed',
            Lockout::class       => 'handleLockout',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
