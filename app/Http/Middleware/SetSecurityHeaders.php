<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Le nonce doit être généré AVANT $next() pour être disponible dans les vues Blade
        $nonce = bin2hex(random_bytes(16));
        view()->share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        $csp = "default-src 'self'; "
             . "script-src 'self' 'nonce-$nonce'; "
             . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
             . "img-src 'self' data:; "
             . "font-src 'self' https://cdnjs.cloudflare.com; "
             . "object-src 'none'; "
             . "base-uri 'self'; "
             . "frame-ancestors 'self'; "
             . "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
