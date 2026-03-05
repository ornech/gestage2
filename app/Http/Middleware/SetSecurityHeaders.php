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

        // Correction de la vulnérabilité MIME Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Empêche l'affichage dans une iframe (Clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }
}
