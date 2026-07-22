<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    private const MAX_ATTEMPTS = 3;
    private const DECAY_SECONDS = 15 * 60;

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('auth.two-factor');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:8', 'regex:/^[A-Za-z0-9@#$%*]{8}$/'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $userId = $request->session()->get('two_factor_user_id');
        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $record = TwoFactorCode::where('user_id', $userId)->first();
        $user = User::find($userId);

        if (! $record || ! $user || $user->status != 1 || ! $user->hasRole('Admin')
            || $record->isExpired() || ! Hash::check($request->input('code'), $record->code_hash)) {
            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid or expired.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $record->delete();

        $remember = (bool) $request->session()->get('two_factor_remember', false);
        Auth::loginUsingId($userId, $remember);

        $intended = $request->session()->get('two_factor_intended');
        if (! is_string($intended) || ! str_contains($intended, '/admin')) {
            $intended = route('admin.dashboard');
        }

        $request->session()->forget([
            'two_factor_user_id',
            'two_factor_remember',
            'two_factor_intended',
        ]);
        $request->session()->regenerate();

        return redirect()->to($intended);
    }

    public function resend(Request $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $userId = $request->session()->get('two_factor_user_id');
        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('admin.login');
        }

        $twoFactorService->send($user);

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'code' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(Request $request): string
    {
        $userId = (string) $request->session()->get('two_factor_user_id', 'guest');

        return Str::transliterate(Str::lower($userId.'|'.$request->ip()));
    }
}
