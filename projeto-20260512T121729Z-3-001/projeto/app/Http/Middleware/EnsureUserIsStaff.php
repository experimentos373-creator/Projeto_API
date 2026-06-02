<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (auth()->user()->user_type === 'A' || auth()->user()->user_type === 'F')) {
            return $next($request);
        }

        abort(403, 'Acesso negado. Apenas funcionários ou administradores podem aceder.');
    }
}
