<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Prevent 'Outlet User' and 'User' roles from logging into the admin portal
        if ($user->hasAnyRole(['Outlet User', 'User'])) {
            Auth::logout();
            return redirect('/')->with('error', 'Your account does not have permission to access the admin portal.');
        }

        $intended = $request->session()->get('url.intended');
        if (! is_string($intended) || ! str_contains($intended, '/admin')) {
            $intended = route('admin.dashboard');
        }

        // If mail isn't configured, allow direct admin access (no 2FA).
        if (! $this->isMailConfigured()) {
            $request->session()->regenerate();
            return redirect()->to($intended);
        }

        // Logout BEFORE OTP send so a mail failure can't leave a logged-in session.
        // Auth::logout();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        // try {
        //     $twoFactorService->send($user);
        // } catch (Throwable $e) {
        //     report($e);

        //     return back()->withErrors([
        //         'email' => 'Unable to send verification code. Please try again.',
        //     ]);
        // }

        // $request->session()->put('two_factor_user_id', $user->id);
        // $request->session()->put('two_factor_remember', $request->boolean('remember'));
        // $request->session()->put('two_factor_intended', $intended);

        // return redirect()->route('admin.two-factor.challenge');

        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    private function isMailConfigured(): bool
    {
        $driver = config('mail.default');
        if (! $driver) {
            return false;
        }

        if ($driver !== 'smtp') {
            return true;
        }

        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');

        return ! empty($host) && ! empty($port) && ! empty($username);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
