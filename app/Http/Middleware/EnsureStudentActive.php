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
        $isSorti    = $user->aQuitteLeCycle();
        $isBloque   = $user->statut === 'demissionnaire';

        if ($isSorti || $isBloque) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $raison = $isSorti
                ? 'Votre formation est terminée. Merci pour votre passage.'
                : 'Votre accès à cette application a été désactivé.';

            return redirect()->route('login')->withErrors(['email' => $raison]);
        }

        return $next($request);
    }
}
