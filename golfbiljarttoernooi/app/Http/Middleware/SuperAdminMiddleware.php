<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle($request, Closure $next)
{
    if (auth()->check() && auth()->user()->role === 'super_admin') {
        return $next($request);
    }
    return redirect('/');  // Of een andere geschikte actie
}
}
