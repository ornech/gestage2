<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. MIME Type Confusion (X-Content-Type-Options)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Clickjacking Protection (X-Frame-Options)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Content Security Policy (CSP) - Version de base
        // Note: À adapter selon vos besoins (si vous utilisez des CDN, scripts externes, etc.)
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self';");

        return $response;
    }
}
