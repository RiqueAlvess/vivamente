<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('tenant.login');
        }

        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('tenant.login')
                ->withErrors(['email' => 'Sua conta está desativada.']);
        }

        return $next($request);
    }
}
