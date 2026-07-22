<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Display the frontend login view.
     */
    public function create(): View
    {
        $sliders = Schema::hasTable('sliders')
            ? Slider::query()
                ->where('status', 1)
                ->orderBy('serial')
                ->get()
            : collect();

        return view('frontend.auth.login', [
            'sliders' => $sliders,
        ]);
    }

    /**
     * Handle an incoming authentication request for Outlet Users and Users.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Only allow Outlet User or User to login on frontend
        if (!$user->hasRole('Outlet User') && !$user->hasRole('User')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This portal is only for Customers and Outlet Users.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
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
