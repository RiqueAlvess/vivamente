<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('tenant')->check()) {
            return redirect()->route('tenant.login');
        }

        $user = Auth::guard('tenant')->user();

        if (! $user->is_active) {
            Auth::guard('tenant')->logout();
            return redirect()->route('tenant.login')
                ->withErrors(['email' => 'Sua conta está desativada.']);
        }

        return $next($request);
    }
}
