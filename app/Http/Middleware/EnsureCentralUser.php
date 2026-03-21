<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('central.login');
        }

        if ($request->user()->role !== 'global_admin') {
            auth()->logout();
            return redirect()->route('central.login')
                ->withErrors(['email' => 'Acesso restrito ao administrador global.']);
        }

        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('central.login')
                ->withErrors(['email' => 'Sua conta está desativada.']);
        }

        return $next($request);
    }
}
