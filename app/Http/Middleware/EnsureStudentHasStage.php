<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentHasStage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('Etudiant')) {
            return $next($request);
        }

        $user = auth()->user();

        // Le journal exige le stage de l'année scolaire active (un stage d'une autre année ne compte pas)
        if (!$user->aUnStageCetteAnnee()) {
            return redirect()->route('etudiant.stages.index')
                ->withErrors([
                    'stage' => 'Vous devez saisir les informations de votre stage avant d\'accéder au journal de stage.',
                ]);
        }

        return $next($request);
    }
}
