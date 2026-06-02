<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /**
     * Only authenticated customers (user_type = 'C') may proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type === 'C') {
            return $next($request);
        }

        abort(403, 'Acesso negado. Esta área é exclusiva para clientes.');
    }
}
