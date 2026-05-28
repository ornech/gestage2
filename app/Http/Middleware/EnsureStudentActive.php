<?php

namespace App\Http\Middleware;

use App\Models\Parametre;
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

        $annee      = Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
        $sy         = (int) explode('-', $annee)[0];
        $isDiplome  = $user->promo && $user->promo <= $sy;
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
