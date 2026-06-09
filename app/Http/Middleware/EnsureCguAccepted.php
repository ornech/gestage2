<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCguAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->check()
            && !auth()->user()->cgu_accepted_at
            && !$request->routeIs('cgu.*')
            && !$request->routeIs('password.first-change', 'password.first-change.update')
            && !$request->routeIs('logout')
        ) {
            return redirect()->route('cgu.show');
        }

        return $next($request);
    }
}
