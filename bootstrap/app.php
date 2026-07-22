<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'verified', 'role:Admin'])
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function () {
            if (Auth::user()?->hasRole('Admin')) {
                return route('admin.dashboard');
            }
            return route('home');
        });
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $exception, $request) {
            // Keep default JSON API error formatting.
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            // Keep Laravel default behavior for validation/auth redirects.
            if ($exception instanceof ValidationException || $exception instanceof AuthenticationException) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? (int) $exception->getStatusCode()
                : 500;

            $isLocal = app()->isLocal();
            $debugPayload = $isLocal ? [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ] : null;

            $meta = match ($status) {
                401 => [
                    'badge' => 'Authentication Required',
                    'heading' => 'You are not authorized for this request.',
                    'message' => 'Please log in with a valid account to continue.',
                ],
                403 => [
                    'badge' => 'Permission Required',
                    'heading' => 'Access denied for this area.',
                    'message' => 'You do not have the required permission to access this page.',
                ],
                404 => [
                    'badge' => 'Not Found',
                    'heading' => 'The page you requested does not exist.',
                    'message' => 'The URL may be incorrect, or the page may have been moved.',
                ],
                419 => [
                    'badge' => 'Session Timeout',
                    'heading' => 'Your session has expired.',
                    'message' => 'Please refresh the page and submit your request again.',
                ],
                429 => [
                    'badge' => 'Rate Limit',
                    'heading' => 'Too many requests from your side.',
                    'message' => 'Please wait a short time and try again.',
                ],
                503 => [
                    'badge' => 'Maintenance',
                    'heading' => 'Service is temporarily unavailable.',
                    'message' => 'The application is under maintenance or temporarily unavailable.',
                ],
                default => [
                    'badge' => $status >= 500 ? 'Server Error' : 'Request Error',
                    'heading' => $status >= 500
                        ? 'An internal server error occurred.'
                        : 'Your request could not be completed.',
                    'message' => $status >= 500
                        ? 'A temporary backend issue occurred while handling this request.'
                        : 'Please review your request and try again.',
                ],
            };

            if ($isLocal && $status >= 500 && $exception->getMessage() !== '') {
                $meta['message'] = $exception->getMessage();
            }

            $statusText = HttpResponse::$statusTexts[$status] ?? 'Error';

            return response()->view('errors.unified', array_merge($meta, [
                'status' => $status,
                'title' => $status . ' | ' . $statusText,
                'debug' => $debugPayload,
            ]), $status);
        });
    })->create();
