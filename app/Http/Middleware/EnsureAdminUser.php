<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role !== 'global_admin') {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Acesso restrito ao administrador global.']);
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Sua conta está desativada.']);
        }

        return $next($request);
    }
}
