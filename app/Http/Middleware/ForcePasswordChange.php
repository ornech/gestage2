<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    private const EXCLUDED_ROUTES = [
        'password.first-change',
        'password.first-change.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()
            && auth()->user()->force_password_change
            && !$request->routeIs(...self::EXCLUDED_ROUTES)
        ) {
            return redirect()->route('password.first-change');
        }

        return $next($request);
    }
}
