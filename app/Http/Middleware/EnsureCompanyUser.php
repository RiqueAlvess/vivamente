<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->company_id) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Usuário não vinculado a nenhuma empresa.']);
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Sua conta está desativada.']);
        }

        return $next($request);
    }
}
