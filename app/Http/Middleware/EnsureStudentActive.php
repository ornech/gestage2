<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('Etudiant')) {
            return $next($request);
        }

        $user = auth()->user();

        // Le diplôme est validé par un professeur : il n'est jamais déduit de la promo.
        $isDiplome  = $user->estDiplome();
        $isBloque   = $user->statut === 'demissionnaire';

        if ($isDiplome || $isBloque) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $raison = $isDiplome
                ? 'Votre formation est terminée. Merci pour votre passage.'
                : 'Votre accès à cette application a été désactivé.';

            return redirect()->route('login')->withErrors(['email' => $raison]);
        }

        return $next($request);
    }
}
