<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function showLoginForm(): Response
    {
        return Inertia::render('Tenant/Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login.' . Str::lower($request->email) . '.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! \Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Credenciais inválidas.']);
        }

        if ($user->isLocked()) {
            return back()->withErrors(['email' => 'Conta bloqueada temporariamente. Tente novamente mais tarde.']);
        }

        if (! $user->is_active) {
            return back()->withErrors(['email' => 'Conta desativada. Entre em contato com o administrador.']);
        }

        $user->resetLoginAttempts();
        RateLimiter::clear($key);

        Auth::guard('tenant')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('tenant.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
